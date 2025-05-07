<?php
$host = 'localhost';
$db = 'digital_library';
$user = 'lector';
$pass = 'lector123'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $sql = "
        SELECT 
    b.book_id,
    b.title,
    b.author,
    b.isbn,
    b.editorial,
    b.year_publication,
    ROUND(AVG(v.score), 2) AS average_score,
    COUNT(v.valoration_id) AS total_valorations,
    l.status AS loan_status,
    b.cover,
    (SELECT GROUP_CONCAT(v2.comments SEPARATOR ', ') 
     FROM Valoration v2 WHERE v2.book_id = b.book_id) AS all_comments,
    (SELECT GROUP_CONCAT(u2.name SEPARATOR ', ') 
     FROM Valoration v2 
     LEFT JOIN Users u2 ON v2.user_id = u2.user_id
     WHERE v2.book_id = b.book_id) AS all_users
FROM Book b
LEFT JOIN Valoration v ON b.book_id = v.book_id
LEFT JOIN BookLoan bl ON b.book_id = bl.book_id
LEFT JOIN Loan l ON bl.loan_id = l.loan_id AND l.status = 'active'
WHERE b.book_id = :book_id
GROUP BY b.book_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['book_id' => $book_id]);
    $book = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
    $sql_ratings = "
    SELECT 
        v.comments,
        v.score,
        u.name AS user_name
    FROM Valoration v
    LEFT JOIN Users u ON v.user_id = u.user_id
    WHERE v.book_id = :book_id
";

$stmt_ratings = $pdo->prepare($sql_ratings);
$stmt_ratings->execute(['book_id' => $book_id]);
$ratings = $stmt_ratings->fetchAll(PDO::FETCH_ASSOC);

    if (!$book) {
        die('Libro no encontrado');
    }
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalles del Libro</title>
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
      .book-details {
        background: #fff;
        border-radius: 16px;
        padding: 28px 24px 24px 24px;
        margin: 0 auto;
        max-width: 600px;
        box-shadow: 0 4px 16px rgba(44, 62, 80, 0.10), 0 1.5px 4px rgba(44,62,80,0.07);
        position: relative;
        border: 1.5px solid #e3e8f0;
        transition: box-shadow 0.18s, border-color 0.18s;
      }
      .book-details:hover {
        box-shadow: 0 8px 32px rgba(44, 62, 80, 0.16), 0 2px 8px rgba(44,62,80,0.10);
        border-color: #b6c6e6;
      }
      .book-details img {
        width: 200px;
        height: 300px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(44,62,80,0.10);
        background: #f6f8fa;
        display: block;
        margin: 0 auto;
      }
      .details-info {
        margin-top: 24px;
        font-size: 1.13em;
        color: #2d3a4b;
      }
      .details-info p {
        margin: 8px 0 0 0;
        line-height: 1.5;
      }
      .loan-status {
        color: #e74c3c;
        font-weight: bold;
        margin-top: 16px;
        font-size: 1.08em;
        letter-spacing: 0.5px;
      }
      .back-btn {
        margin-top: 28px;
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
      .back-btn:hover {
        background: linear-gradient(90deg, #0056b3 0%, #4f8cff 100%);
        transform: scale(1.05);
      }
      .ratings {
        margin-top: 38px;
        background-color: #f9faff;
        padding: 20px 18px 10px 18px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(44,62,80,0.08);
        border: 1px solid #e3e8f0;
      }
      .ratings h3 {
        color: #2d3a4b;
        margin-bottom: 18px;
        font-size: 1.25em;
        letter-spacing: 0.5px;
      }
      .rating {
        margin-bottom: 18px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e3e8f0;
      }
      .rating:last-child {
        border-bottom: none;
      }
      .rating p {
        margin: 0;
      }
      .rating .score {
        font-weight: bold;
        color: #FF9800;
        margin-top: 2px;
      }
      .rating .comment {
        font-style: italic;
        color: #555;
        margin-top: 2px;
      }
    </style>
</head>
<body>
  <h1>Detalles del Libro</h1>

  <div class="book-details">
    <img src="data:image/jpeg;base64,<?= base64_encode($book[0]['cover']) ?>" alt="Portada de <?= htmlspecialchars($book[0]['title']) ?>">
    <h2><?= htmlspecialchars($book[0]['title']) ?></h2>
    <div class="details-info">
      <p><strong>Autor:</strong> <?= htmlspecialchars($book[0]['author']) ?></p>
      <p><strong>ISBN:</strong> <?= htmlspecialchars($book[0]['isbn']) ?></p>
      <p><strong>Editorial:</strong> <?= htmlspecialchars($book[0]['editorial']) ?></p>
      <p><strong>Año de publicación:</strong> <?= date('Y', strtotime($book[0]['year_publication'])) ?></p>
      <p><strong>Valoración media:</strong> <?= $book[0]['average_score'] ?? 'N/A' ?> (<?= $book[0]['total_valorations'] ?> valoración/es)</p>
    </div>
    <?php if ($book[0]['loan_status']): ?>
      <div class="loan-status">Actualmente prestado (<?= htmlspecialchars($book[0]['loan_status']) ?>)</div>
    <?php endif; ?>
    <button class="back-btn" onclick="window.location.href='index.php'">Volver al listado</button>
  </div>

  <div class="ratings">
    <h3>Valoraciones de los Usuarios</h3>
    <?php foreach ($ratings as $rating): ?>
      <div class="rating">
        <p><strong><?= htmlspecialchars($rating['user_name']) ?>:</strong></p>
        <p class="score">Puntuación: <?= htmlspecialchars($rating['score']) ?>/5</p>
        <p class="comment"><?= htmlspecialchars($rating['comments']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
