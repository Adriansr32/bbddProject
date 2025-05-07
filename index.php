<?php
$host = 'localhost';
$db = 'digital_library';
$user = 'lector';
$pass = 'lector123'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        SELECT 
            b.book_id,
            b.title,
            b.cover
        FROM Book b
    ";

    $stmt = $pdo->query($sql);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Digital library</title>
  <link rel="icon" type="image/jpeg" href="img/DL.png">
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <h1>Available books</h1>
  <div class="book-grid">
    <?php foreach ($books as $book): ?>
      <div class="book">
        <img src="data:image/jpeg;base64,<?= base64_encode($book['cover']) ?>" alt="Portada de <?= htmlspecialchars($book['title']) ?>">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <button class="details-btn" onclick="window.location.href='/details/book_details.php?id=<?= $book['book_id'] ?>'">View details</button>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
