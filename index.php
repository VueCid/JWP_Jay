<?php
session_start();

// ---------------------------------------------
// INISIALISASI DATA AWAL
// ---------------------------------------------
if (!isset($_SESSION["tasks"])) {
    $_SESSION["tasks"] = [
        ["id" => 1, "nama" => "Belajar PHP", "status" => false],
        ["id" => 2, "nama" => "Kerjakan Tugas UX", "status" => false]
    ];
}

$tasks = &$_SESSION["tasks"];

// ---------------------------------------------
// HANDLE AKSI
// ---------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["task"])) {
    $newTask = trim($_POST["task"]);
    if ($newTask !== "") {
        $newId = count($tasks) ? max(array_column($tasks, "id")) + 1 : 1;
        $tasks[] = ["id" => $newId, "nama" => htmlspecialchars($newTask), "status" => false];
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET["hapus"])) {
    $hapusId = (int)$_GET["hapus"];
    $tasks = array_values(array_filter($tasks, fn($t) => $t["id"] !== $hapusId));
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["edit_id"], $_POST["edit_nama"])) {
    $editId = (int)$_POST["edit_id"];
    $editNama = trim($_POST["edit_nama"]);
    foreach ($tasks as &$t) {
        if ($t["id"] === $editId && $editNama !== "") {
            $t["nama"] = htmlspecialchars($editNama);
        }
    }
    unset($t);
    header("Location: index.php");
    exit;
}

if (isset($_GET["toggle"])) {
    $toggleId = (int)$_GET["toggle"];
    foreach ($tasks as &$t) {
        if ($t["id"] === $toggleId) {
            $t["status"] = !$t["status"];
        }
    }
    unset($t);
    header("Location: index.php");
    exit;
}

if (isset($_GET["reset"])) {
    foreach ($tasks as &$t) {
        $t["status"] = false;
    }
    unset($t);
    header("Location: index.php");
    exit;
}

$_SESSION["tasks"] = $tasks;

// ---------------------------------------------
// FUNGSI RENDER
// ---------------------------------------------
function tampilkanDaftar($tasks) {
    if (empty($tasks)) {
        echo "<div class='alert alert-info'>Belum ada tugas.</div>";
        return;
    }

    echo "<ul class='list-group'>";
    foreach ($tasks as $task) {
        if (!isset($task["nama"])) continue;

        $checked = $task["status"] ? "checked" : "";
        $doneText = $task["status"] ? "text-decoration-line-through text-muted" : "";

        $hapusButton = $task["status"]
            ? "<a href='?hapus={$task['id']}' class='btn btn-sm btn-danger me-1'>Hapus</a>"
            : "<button class='btn btn-sm btn-danger me-1' disabled>Hapus</button>";

        $editButton = "<button class='btn btn-sm btn-warning' onclick=\"toggleEditForm({$task['id']})\">Edit</button>";

        echo "
        <li class='list-group-item d-flex justify-content-between align-items-center'>
            <div class='d-flex align-items-center'>
                <input type='checkbox' id='task-{$task['id']}' 
                       class='form-check-input me-2' 
                       onclick=\"window.location='?toggle={$task['id']}'\" $checked 
                       title='Tandai selesai'>
                <label for='task-{$task['id']}' class='$doneText mb-0' id='label-{$task['id']}'>
                    {$task['nama']}
                </label>

                <!-- Form edit -->
                <form method='POST' class='d-none ms-3' id='form-{$task['id']}'>
                    <input type='hidden' name='edit_id' value='{$task['id']}'>
                    <div class='input-group input-group-sm'>
                        <input type='text' class='form-control' name='edit_nama' 
                               value='{$task['nama']}' 
                               placeholder='Edit nama tugas' 
                               aria-label='Edit nama tugas' required>
                        <button type='submit' class='btn btn-primary'>Simpan</button>
                        <button type='button' class='btn btn-secondary' onclick=\"toggleEditForm({$task['id']})\">Batal</button>
                    </div>
                </form>
            </div>
            <div>
                $hapusButton
                $editButton
            </div>
        </li>";
    }
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Daftar Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function toggleEditForm(id) {
            const form = document.getElementById('form-' + id);
            form.classList.toggle('d-none');
        }
    </script>
</head>
<body class="bg-light">

<div class="container py-4">
    <header class="mb-4 text-center">
        <h1 class="display-6">📋 Daftar Tugas</h1>
    </header>

    <!-- FORM TAMBAH -->
    <form method="POST" class="row g-2 mb-4">
        <div class="col-9 col-sm-10">
            <label for="taskInput" class="visually-hidden">Tambah tugas baru</label>
            <input type="text" id="taskInput" name="task" 
                   class="form-control" 
                   placeholder="Tambah tugas baru..." 
                   aria-label="Tambah tugas baru" required>
        </div>
        <div class="col-3 col-sm-2 d-grid">
            <button type="submit" class="btn btn-success">Tambah</button>
        </div>
    </form>

    <!-- RESET STATUS -->
    <div class="mb-3 text-end">
        <a href="?reset=1" class="btn btn-warning btn-sm">Reset Semua Checklist</a>
    </div>

    <!-- DAFTAR -->
    <section>
        <h5 class="mb-3">Daftar:</h5>
        <?php tampilkanDaftar($tasks); ?>
    </section>
</div>

</body>
</html>
