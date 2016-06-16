select count(*) as ROWS from(SELECT row_number() over(order by scores.studentno) as row,scores.studentno as  学号,students.name as 姓名,year,year+1 as year2 ,term,
scores.ps as 平时,scores.qm 期末, case[testscore] when '' then cast(cast(scores.EXAMSCORE as int) as char(3)) else testscore end AS 总评,scores.edate as 考试时间,
datename(yyyy,scores.date)+'-'+rtrim(cast(datepart(mm,scores.date) as char(2)))+'-'+DATENAME(DD, scores.date) as 填表时间
FROM scores inner join students on students.studentno=scores.studentno
WHERE YEAR= :YEAR
AND TERM= :TERM
AND scores.courseno+scores.[group] =:COURSENO
) as b
