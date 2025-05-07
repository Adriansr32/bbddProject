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
  <title>Biblioteca Digital</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: linear-gradient(135deg, #e0e7ff 0%, #f2f2f2 100%);
      margin: 0;
      padding: 20px;
      min-height: 100vh;
    }
    h1 {
      text-align: center;
      color: #2d3a4b;
      letter-spacing: 1px;
      margin-bottom: 30px;
      font-size: 2.5em;
      text-shadow: 0 2px 8px #b6c6e6;
    }
    .book-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 28px;
      margin-top: 20px;
      padding: 10px;
    }
    .book {
      background: #fff;
      border-radius: 16px;
      padding: 18px 15px 20px 15px;
      box-shadow: 0 4px 16px rgba(44, 62, 80, 0.10), 0 1.5px 4px rgba(44,62,80,0.07);
      text-align: center;
      transition: transform 0.18s, box-shadow 0.18s;
      border: 1.5px solid #e3e8f0;
      position: relative;
    }
    .book:hover {
      transform: translateY(-6px) scale(1.03);
      box-shadow: 0 8px 32px rgba(44, 62, 80, 0.16), 0 2px 8px rgba(44,62,80,0.10);
      border-color: #b6c6e6;
    }
    .book img {
      max-width: 100%;
      height: 220px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(44,62,80,0.10);
      background: #f6f8fa;
    }
    .book h3 {
      margin-top: 14px;
      font-size: 1.18em;
      color: #2d3a4b;
      font-weight: 600;
      min-height: 48px;
      letter-spacing: 0.5px;
    }
    .details-btn {
      margin-top: 14px;
      padding: 10px 18px;
      background: linear-gradient(90deg, #4f8cff 0%, #007BFF 100%);
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1em;
      font-weight: 500;
      box-shadow: 0 1px 4px rgba(44,62,80,0.07);
      transition: background 0.18s, transform 0.13s;
    }
    .details-btn:hover {
      background: linear-gradient(90deg, #0056b3 0%, #4f8cff 100%);
      transform: scale(1.05);
    }
  </style>
</head>
<body>
  <h1>Libros Disponibles</h1>
  <div class="book-grid">
    <?php foreach ($books as $book): ?>
      <div class="book">
        <img src="data:image/jpeg;base64,<?= base64_encode($book['cover']) ?>" alt="Portada de <?= htmlspecialchars($book['title']) ?>">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <button class="details-btn" onclick="window.location.href='book_details.php?id=<?= $book['book_id'] ?>'">Ver detalles</button>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
