select count(*) as ROWS from(select students.studentno 学号,students.name 姓名, isnull(coursecredit,0)+isnull(addcredit,0) as 素质学分 from
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
)as b