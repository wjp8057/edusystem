select count(*) as ROWS from (select row_number() over(order by ROOMNO) as row,ROOMNO,KW,louNO,menpaiNO,ROOMNAME,RECNO,status from TESTROOM
WHERE ROOMNO LIKE :ROOMNO AND ISNULL(ROOMNAME,'') LIKE :ROOMNAME AND ISNULL(menpaiNO,'') LIKE :MENPAI
)as b