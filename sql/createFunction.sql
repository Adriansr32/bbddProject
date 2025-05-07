-- ------------------------------
-- FUNCIÓ PER REGISTRAR LLIBRES PRESTATS EN LOANLOG
-- ------------------------------
DELIMITER $$

CREATE PROCEDURE RegisterLoan(IN p_loan_id INT, IN p_user_id INT)
BEGIN
    INSERT INTO LoanLog (loan_id, user_id, action)
    VALUES (p_loan_id, p_user_id, 'INSERT');
END $$

DELIMITER ;