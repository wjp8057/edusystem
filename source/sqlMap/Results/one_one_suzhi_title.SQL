select coursecredit+isnull(addcredit,0) as credits from (select sum(credits) coursecredit,studentno from scores inner join courses on courses.courseno=scores.courseno
where (scores.courseno like '08%' or scores.courseno like '007%' or scores.[group] like 'G_') and
(testscore in ('合格','及格','中等','良好','优秀') or examscore >=60) and studentno=:Sone
group by studentno) as temp left outer join  (select sum(credit) as addcredit,studentno
from addcredit where studentno=:Stwo
group by studentno) as temp2 on temp.studentno=temp2.studentno