<?php

session_start();
require_once 'config.php';

// Get the logged-in user's ID (adjust this to your session logic)
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit();
}

$recipes_dir = __DIR__ . '/user_recipes';
if (!is_dir($recipes_dir)) {
    mkdir($recipes_dir, 0755, true);
}
$user_json_file = $recipes_dir . "/user_recipes_{$user_id}.json";

// Helper: Load or create user's recipe JSON
function load_user_recipes($file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) return $data;
    }
    return ['recipes' => []];
}

// Helper: Save user's recipe JSON
function save_user_recipes($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_all_recipes'])) {
        // Clear the user's recipes JSON file
        $empty = ['recipes' => []];
        save_user_recipes($user_json_file, $empty);
        $pattern = __DIR__ . "/Bilder/{$user_id}_*.webp";
        foreach (glob($pattern) as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Optionally, update the DB column as well
        $json_content = json_encode($empty, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("UPDATE users SET user_recipes = ? WHERE id = ?");
        $stmt->bind_param("si", $json_content, $user_id);
        $stmt->execute();
        $stmt->close();

        header('Location: index.php');
        exit();
    }
    
    if (isset($_POST['delete_recipe_id'])) {
        $delete_id = intval($_POST['delete_recipe_id']);
        $data = load_user_recipes($user_json_file);
        $data['recipes'] = array_values(array_filter($data['recipes'], function($r) use ($delete_id) {
            return intval($r['id']) !== intval($delete_id);
        }));
        save_user_recipes($user_json_file, $data);

        // Optionally update DB column as well
        $json_content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("UPDATE users SET user_recipes = ? WHERE id = ?");
        $stmt->bind_param("si", $json_content, $user_id);
        $stmt->execute();
        $stmt->close();

        exit(); // No redirect needed for fetch
    }

    if (isset($_POST['recipe-name'], $_POST['recipe-ingredients'], $_POST['recipe-instructions'])) {
        // Manual recipe
        $name = $_POST['recipe-name'];
        $ingredients = $_POST['recipe-ingredients'];
        $instructions = $_POST['recipe-instructions'];

        // Load, add, and save to user's JSON
        $data = load_user_recipes($user_json_file);
        $ids = array_column($data['recipes'], 'id');
        $nextId = $ids ? max($ids) + 1 : 1;
        $data['recipes'][] = [
            'id' => $nextId,
            'title' => $name,
            'image' => '',
            'description' => $ingredients,
            'instructions' => $instructions,
            'ingredients' => $ingredients,
            'source_url' => ''
        ];
        save_user_recipes($user_json_file, $data);

        // Optionally, store the JSON content or file path in the DB
        $json_content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("UPDATE users SET user_recipes = ? WHERE id = ?");
        $stmt->bind_param("si", $json_content, $user_id);
        $stmt->execute();
        $stmt->close();

    } elseif (isset($_POST['recipe-link'])) {
        // Get the link from the form
        $submitted_url = $_POST['recipe-link'];
        $data = load_user_recipes($user_json_file);
        $already_exists = false;
        foreach ($data['recipes'] as $recipe) {
            if (isset($recipe['source_url']) && $recipe['source_url'] === $submitted_url) {
                $already_exists = true;
                break;
            }
        }
        if ($already_exists) {
            echo "<script>alert('Detta recept är redan tillagt!'); window.location.href='index.php';</script>";
            exit();
        }
        $url = escapeshellarg($_POST['recipe-link']);
        $python = 'C:\Python312\python.exe';
        $script = __DIR__ . '/recept-scraper.py';

        // Run the Python script and capture the output
        $output = shell_exec("$python $script $url $user_id");
        $output = mb_convert_encoding($output, 'UTF-8', 'UTF-8');
        $scraped = json_decode($output, true);

        if (!$scraped) {
            file_put_contents(__DIR__ . '/debug.log', "JSON DECODE FAILED. Raw output: $output\n", FILE_APPEND);
        } else if (isset($scraped['title'], $scraped['instructions'])) {
            // Load, add, and save to user's JSON
            $data = load_user_recipes($user_json_file);
            $ids = array_column($data['recipes'], 'id');
            $nextId = $ids ? max($ids) + 1 : 1;
            $data['recipes'][] = [
                'id' => $nextId,
                'title' => $scraped['title'],
                'image' => $scraped['image'],
                'description' => '', // You can extend your scraper to get description
                'instructions' => $scraped['instructions'],
                'ingredients' => $scraped['ingredients'],
                'source_url' => $_POST['recipe-link'] // Store the original URL
            ];
            save_user_recipes($user_json_file, $data);

            // Optionally, update the DB column as well
            $json_content = json_encode($data, JSON_UNESCAPED_UNICODE);
            $stmt = $conn->prepare("UPDATE users SET user_recipes = ? WHERE id = ?");
            $stmt->bind_param("si", $json_content, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: index.php');
    exit();
}

