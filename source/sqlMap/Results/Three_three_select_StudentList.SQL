select * from(SELECT row_number() over(order by scores.studentno) as row,scores.studentno as xh,students.name as xm,
--ps as pscj,
qm as qmcj,
--case [testscore]
--when '' then cast(scores.EXAMSCORE as char(5))
--else scores.testscore
--end as  zpcj,
examrem as zt,0 as flag,RECNO,scores.lock,
convert(varchar(10),scores.edate,20) as ksrq
FROM scores,students
WHERE YEAR=:year
AND TERM=:term
AND COURSENO+[group] =:courseno
and scores.studentno=students.studentno
) as b where b.row between :start and :end