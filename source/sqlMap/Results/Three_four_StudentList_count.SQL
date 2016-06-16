select count(*) as ROWS from(SELECT row_number() over(order by scores.studentno)as row,scores.studentno as 学号,students.name as 姓名,ps as 平时成绩,qm as 期末成绩,
case [testscore]
when '' then cast(scores.EXAMSCORE as char(5))
else scores.testscore
end as  总评成绩,
examrem as 状态,0 as flag,RECNO,scores.lock,scores.edate as 考试日期
FROM scores,students
WHERE YEAR=:year
AND TERM=:term
AND COURSENO+[group] =:courseno
and scores.studentno=students.studentno
)as b