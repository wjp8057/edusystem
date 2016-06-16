select count(*) as ROWS from(SELECT row_number() over(order by scores.studentno) as row,scores.studentno as xh,students.name as xm,scores.year,scores.year+1 as year2 ,scores.term,
case [testscore2]
when '' then '0'
else '无'
end  as pd,
case [testscore2]
when '' then cast(scores.EXAMSCORE2 as char(5))
else scores.testscore2
end as qm,
case[testscore2] when '' then cast(cast(scores.EXAMSCORE2 as int) as char(3)) else scores.testscore2 end AS 总评,makeup.edate as 考试时间,datename(yyyy,makeup.date)+'-'+rtrim(cast(datepart(mm,makeup.date) as char(2)))+'-'+DATENAME(DD, makeup.date) as 填表时间
FROM scores inner join students on students.studentno=scores.studentno
inner join makeup on makeup.courseno=scores.courseno and makeup.studentno=scores.studentno and
makeup.year=scores.year and makeup.term=scores.term
WHERE scores.YEAR= :YEAR
AND scores.TERM= :TERM
AND makeup.courseno =:COURSENO
AND SCORES.[GROUP]!='BY'
) as b