-- ------------------------------
-- CREACIÓ DE TABLAS
-- ------------------------------

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash TEXT,
    role ENUM('user', 'employee', 'admin') DEFAULT 'user'
);

CREATE TABLE Book (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    author VARCHAR(100),
    isbn VARCHAR(20) UNIQUE NOT NULL,
    cover BLOB,
    editorial VARCHAR(100),
    year_publication DATE
);

CREATE TABLE Loan (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_loan DATE,
    date_return DATE,
    status ENUM('active', 'return', 'later') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE BookLoan (
    loan_id INT NOT NULL,
    book_id INT NOT NULL,
    PRIMARY KEY (loan_id, book_id),
    FOREIGN KEY (loan_id) REFERENCES Loan(loan_id),
    FOREIGN KEY (book_id) REFERENCES Book(book_id)
);

CREATE TABLE LoanLog (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT,
    user_id INT,
    action VARCHAR(50),
    log_time DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Valoration (
    valoration_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    score INT CHECK (score BETWEEN 1 AND 5),
    comments TEXT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Book(book_id)
);