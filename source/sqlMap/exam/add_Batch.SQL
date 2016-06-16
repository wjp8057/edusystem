insert into TESTBATCH(FLAG,[YEAR],TERM)
select
case WHEN  max(FLAG) >= 0 THEN max(FLAG)+1
    WHEN   max(FLAG) is NULL THEN 0
END
,:year  as  YEAR,:term as TERM from TESTBATCH