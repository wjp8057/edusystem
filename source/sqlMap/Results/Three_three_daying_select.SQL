select * from(SELECT row_number() over(order by scores.studentno) as row,scores.studentno as  xh,students.name as xm,year,year+1 as year2 ,term,
scores.ps as ps,scores.qm qm, case[testscore] when '' then cast(cast(scores.EXAMSCORE as int) as char(3))
else testscore end AS zp,
convert(varchar(10),scores.edate,20) as kssj,
datename(yyyy,scores.date)+'-'+rtrim(cast(datepart(mm,scores.date) as char(2)))+'-'+DATENAME(DD, scores.date) as tbsj
FROM scores inner join students on students.studentno=scores.studentno
WHERE YEAR= :YEAR
AND TERM= :TERM
AND scores.courseno+scores.[group] =:COURSENO
) as b where b.row between :start and :end
