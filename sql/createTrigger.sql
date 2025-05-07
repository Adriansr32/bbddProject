-- ------------------------------
-- TRIGGER DE LOG DE INSERCIÓ EN PRESTECS
-- ------------------------------

DELIMITER $$

CREATE OR REPLACE TRIGGER trg_log_loan_insert
AFTER INSERT ON Loan
FOR EACH ROW
BEGIN
    CALL RegisterLoan(NEW.loan_id, NEW.user_id);
END $$

DELIMITER ;