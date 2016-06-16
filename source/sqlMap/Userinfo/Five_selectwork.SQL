select * from (SELECT row_number() over(order by COURSENO)as row,YEAR as xn,TERM xq,COURSENO kh,COURSENAME km,WORK gzl,ATTENDENT rs,CLASSNAME bj
FROM 工作量存档 INNER JOIN USERS ON USERS.TEACHERNO=工作量存档.TEACHERNO
WHERE USERS.USERNAME=:USERNAME
)as b where b.row between :start and :end