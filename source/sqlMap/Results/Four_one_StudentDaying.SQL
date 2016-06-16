select * from(SELECT row_number() over(order by scores.studentno) as row,scores.studentno as xh,students.name as xm,
case [testscore2]
when '' then '0'
else '无'
end  as pscj,
case [testscore2]
when '' then cast(scores.EXAMSCORE2 as char(5))
else scores.testscore2
end as qmcj,
case [testscore2]
when '' then cast(scores.EXAMSCORE2 as char(5))
else scores.testscore2
end as  zpcj,
examrem as 状态,0 as flag,RECNO,makeup.lock,makeup.edate as ksrq
FROM scores inner join students on scores.studentno=students.studentno
inner join makeup on makeup.studentno=scores.studentno and makeup.courseno=scores.courseno
and makeup.year=scores.year and makeup.term=scores.term
WHERE scores.YEAR=:year
AND scores.TERM=:term
AND makeup.COURSENO=:courseno
AND SCORES.[GROUP]!='BY'
)as b where b.row between :start and :end

