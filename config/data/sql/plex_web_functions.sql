DELIMITER $$
CREATE DEFINER=`bjorn`@`%` FUNCTION `nextseq`(`seq_name` VARCHAR(100)) RETURNS bigint
    DETERMINISTIC
begin
    DECLARE cur_val bigint(20);

SELECT
        cur_value INTO cur_val
    FROM
        sequence
    WHERE
        name = seq_name
    ;
 
    IF cur_val IS NOT NULL THEN
        UPDATE
            sequence
        SET
            cur_value = IF (
                (cur_value + increment) > max_value,
                IF (
                    cycle = TRUE,
                    min_value,
                    NULL
                ),
                cur_value + increment
            )
        WHERE
            name = seq_name
        ;
    END IF;
 
    RETURN cur_val;
    end$$
DELIMITER ;


DELIMITER $$
CREATE DEFINER=`bjorn`@`%` FUNCTION `Today`() RETURNS datetime
    NO SQL
    DETERMINISTIC
begin 
RETURN  CURRENT_DATE();
end$$
DELIMITER ;