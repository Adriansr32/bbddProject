<?php
function obtenerDetallesLibro($pdo, $book_id) {
    $sql = "SELECT 
        b.book_id,
        b.title,
        b.author,
        b.isbn,
        b.editorial,
        b.year_publication,
        ROUND(AVG(v.score), 2) AS average_score,
        COUNT(v.valoration_id) AS total_valorations,
        l.status AS loan_status,
        b.cover
    FROM Book b
    LEFT JOIN Valoration v ON b.book_id = v.book_id
    LEFT JOIN BookLoan bl ON b.book_id = bl.book_id
    LEFT JOIN Loan l ON bl.loan_id = l.loan_id AND l.status = 'active'
    WHERE b.book_id = :book_id
    GROUP BY b.book_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function obtenerValoraciones($pdo, $book_id) {
    $sql = "SELECT v.comments, v.score, u.name AS user_name 
            FROM Valoration v 
            LEFT JOIN Users u ON v.user_id = u.user_id
            WHERE v.book_id = :book_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['book_id' => $book_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
