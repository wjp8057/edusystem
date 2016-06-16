select * from(select row_number() over(order by isnull(temp2.total,0) desc,students.studentno) as row,'' as xuh, students.studentno as xh,students.name as xm,case isnull(temp2.total,0) when '.0' then '0' else convert(varchar,temp2.total,5) end as zxf
from students left outer join (
select temp.studentno,sum(total) as total
from
(select scores.studentno,sum(courses.credits) as total
from  scores inner join courses on courses.courseno=scores.courseno
where  (scores.courseno like '08%' or scores.courseno like '007%') and scores.year=:YONE
and (scores.examscore>=60 or scores.testscore in ('合格','及格','优秀','良好','中等') )
group by scores.studentno
union all
select addcredit.studentno,sum(addcredit.credit) as total
from addcredit
where year=:YONE
group by addcredit.studentno
) as temp group by temp.studentno ) as temp2 on temp2.studentno =students.studentno
where students.classno=:CLASSNO
)as b where b.row between :start and :end