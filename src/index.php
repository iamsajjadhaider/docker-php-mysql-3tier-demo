<?php
// --- Configuration & Initialization ---
$db_host = getenv('DB_HOST') ?: 'db'; // Uses the service name defined in docker-compose
$db_name = getenv('DB_NAME') ?: 'devops_snippet_db';
$db_user = getenv('DB_USER') ?: 'user';
$db_pass = getenv('DB_PASSWORD') ?: 'password123';

// Attempt to establish a connection
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    // In a production environment, you would log this and show a generic error page.
    $error_message = "Database connection failed: " . $mysqli->connect_error;
    http_response_code(500); // 500 Internal Server Error
} else {
    $error_message = null;
}

// Function to handle saving the snippet
function saveSnippet($mysqli) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code']) && isset($_POST['lang'])) {
        $code = $_POST['code'];
        $lang = $_POST['lang'];

        // Basic validation and preparation
        if (empty($code)) {
            return "Please paste some code to save.";
        }
        $lang = strtolower(substr(trim($lang), 0, 50));
        $code_safe = $mysqli->real_escape_string($code); // SQL injection prevention

        // Generate a unique ID (random 8 character hex string)
        $snippet_id = bin2hex(random_bytes(4));

        $stmt = $mysqli->prepare("INSERT INTO snippets (id, code_content, lang_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $snippet_id, $code_safe, $lang);

        if ($stmt->execute()) {
            return "Snippet saved! Shareable Link: <a href='?id={$snippet_id}' class='text-blue-600 hover:text-blue-800 font-medium'>/?id={$snippet_id}</a>";
        } else {
            return "Error saving snippet: " . $stmt->error;
        }
    }
    return null;
}

// Function to handle retrieving and displaying a snippet
function displaySnippet($mysqli, $id) {
    if (empty($id)) {
        return null;
    }

    $id_safe = $mysqli->real_escape_string($id);

    $stmt = $mysqli->prepare("SELECT code_content, lang_type FROM snippets WHERE id = ?");
    $stmt->bind_param("s", $id_safe);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $snippet = $result->fetch_assoc();
        
        // **MIND-OPENER: DATA SANITIZATION**
        // Use htmlspecialchars() to escape any potentially malicious HTML/JS in the saved code.
        // This is CRITICAL for preventing Cross-Site Scripting (XSS).
        $escaped_code = htmlspecialchars($snippet['code_content'], ENT_QUOTES, 'UTF-8');
        $lang = $snippet['lang_type'] ?: 'markup'; // Default to markup if no language is set

        return "
            <h2 class='text-xl font-semibold mb-4 text-gray-700'>Snippet ID: <span class='text-purple-600'>{$id}</span></h2>
            <div class='bg-white shadow-lg rounded-xl overflow-hidden'>
                <pre class='p-4 text-sm whitespace-pre-wrap rounded-xl transition-all duration-300 transform hover:scale-[1.01]'>
                    <code class='language-{$lang}'>{$escaped_code}</code>
                </pre>
            </div>
            <p class='mt-4 text-sm text-gray-500'>**Learning Point:** The code above was sanitized using `htmlspecialchars()` before being placed inside the `<code>` tag, which prevents potential HTML injection attacks (XSS) from malicious saved code.</p>
        ";
    } else {
        return "<p class='text-red-500'>Snippet with ID '{$id}' not found.</p>";
    }
}

$submission_status = $error_message ? null : saveSnippet($mysqli);
$snippet_display = $error_message ? null : displaySnippet($mysqli, $_GET['id'] ?? null);

// Close connection
if ($mysqli && !$mysqli->connect_error) {
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevOps Snippet Tool</title>
    <!-- Load Tailwind CSS via CDN for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Load Prism.js for syntax highlighting of the displayed code -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-twilight.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; }
        .container-box { max-width: 900px; }
        pre[class*="language-"] {
            background: #1e1e1e !important; /* Dark background for code */
            color: #d4d4d4;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="container-box mx-auto">
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">DevOps Learning Project: Code Snippet Tool</h1>
            <p class="text-gray-600 mt-2">A 3-tier containerized application demonstrating Docker networking and persistence.</p>
        </header>

        <!-- Display Area -->
        <main class="mb-10">
            <?php if ($snippet_display): ?>
                <div class="p-6 bg-gray-100 rounded-xl shadow-inner mb-8">
                    <?php echo $snippet_display; ?>
                    <a href="/" class="mt-6 inline-block text-sm text-indigo-600 hover:text-indigo-800 font-medium border-b border-indigo-600">
                        &larr; Back to Submission Form
                    </a>
                </div>
            <?php else: ?>
                <!-- Submission Form Area -->
                <div class="p-6 bg-white rounded-xl shadow-lg">
                    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Save a New Code Snippet</h2>

                    <?php if ($error_message): ?>
                        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 font-medium" role="alert">
                            <span class="font-bold">System Error:</span> <?php echo $error_message; ?>
                            <p class="text-xs mt-1">Check the `db` container logs or ensure the database healthcheck passed.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission_status): ?>
                        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 font-medium" role="alert">
                            <span class="font-bold">Success!</span> <?php echo $submission_status; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/" class="space-y-4">
                        <div>
                            <label for="lang" class="block text-sm font-medium text-gray-700 mb-1">Code Language (e.g., python, javascript, php)</label>
                            <input type="text" id="lang" name="lang" required maxlength="50" placeholder="python" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Paste Your Code</label>
                            <textarea id="code" name="code" rows="15" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-4 border font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., print('Hello DevOps!')"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-medium transition duration-150 ease-in-out">
                            Save Snippet
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <!-- Re-runs Prism highlighting after the DOM is loaded to apply formatting to the newly rendered code -->
    <script>
        window.onload = () => {
            if (Prism) {
                Prism.highlightAll();
            }
        };
    </script>
</body>
</html>
