select * from(SELECT row_number() over(order by scores.studentno) as row,scores.studentno as xh,students.name as xm,scores.year,scores.year+1 as year2 ,scores.term,
-- case
--   when [testscore2] = '' then '0'
--   when [testscore2] is null then '0'
--   else '无'
--   end  as ps,
-- case
--   when [testscore] = '' then cast(scores.EXAMSCORE as char(5))
--   when [testscore] is null then cast(scores.EXAMSCORE as char(5))
--   else scores.testscore
--   end as qm,
case
  when [testscore2] = '' then cast(scores.EXAMSCORE2 as char(5))
  when [testscore2] is null then cast(scores.EXAMSCORE2 as char(5))
  else scores.testscore2
  end AS zp,
convert(varchar(10),makeup.edate,20) as kssj,datename(yyyy,makeup.date)+'-'+rtrim(cast(datepart(mm,makeup.date) as char(2)))+'-'+DATENAME(DD, makeup.date) as tbsj
FROM scores inner join students on students.studentno=scores.studentno
inner join makeup on makeup.courseno=scores.courseno and makeup.studentno=scores.studentno and
makeup.year=scores.year and makeup.term=scores.term
WHERE scores.YEAR= :YEAR
AND scores.TERM= :TERM
AND makeup.courseno =:COURSENO
AND SCORES.[GROUP]!='BY'
) as b where b.row between :start and :end