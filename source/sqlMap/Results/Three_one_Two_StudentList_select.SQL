select * from scores.studentno as xh,students.name as xm,

-- case [testscore2]
-- when '' then '0'
-- else '无'
-- end  as pscj,

case [testscore2]
when '' then cast(scores.EXAMSCORE2 as char(5))
else scores.testscore2
end as qmcj,

-- case [testscore2]
-- when '' then cast(scores.EXAMSCORE2 as char(5))
-- else scores.testscore2
-- end as zpcj,

-- examrem as zt,
0 as flag,RECNO,makeup.lock,
convert(varchar(10),makeup.edate,20) as ksrq
FROM scores inner join students on scores.studentno=students.studentno
inner join makeup on makeup.studentno=scores.studentno and makeup.courseno=scores.courseno
and makeup.year=scores.year and makeup.term=scores.term
WHERE scores.YEAR=:year
AND scores.TERM=:term
AND makeup.COURSENO=:courseno
AND SCORES.[GROUP]!='BY'
) as b where b.row between :start and :end