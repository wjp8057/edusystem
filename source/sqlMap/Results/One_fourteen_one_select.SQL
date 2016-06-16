select * from(select row_number() over(order by isnull(coursecredit,0)+isnull(addcredit,0)) as row,students.studentno xh,students.name xm, isnull(coursecredit,0)+isnull(addcredit,0) as szxf from
students left outer join
(select  sum(credits) coursecredit,scores.studentno studentno
from scores inner join courses on courses.courseno=scores.courseno
where (scores.courseno like '08%' or scores.courseno like '007%') and
(testscore in ('合格','及格','中等','良好','优秀') or examscore >=60)
group by scores.studentno) as temp on temp.studentno=students.studentno
left outer join (select sum(credit) as addcredit,studentno
from addcredit
group by studentno) as temp2 on temp2.studentno=students.studentno
where students.classno =:CLASSNO
)as b where b.row between :start and :end