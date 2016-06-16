--放入全体学生名单
insert into makingcredit(studentno)
select students.studentno
from students where not exists (select * from makingcredit where makingcredit.studentno=students.studentno)
--更新素质学分的汇总的创新技能素质学分部分
update makingcredit
set addcredit=addtion.amount
from makingcredit inner join (select sum(credit) as amount,studentno as studentno from addcredit group by studentno ) as addtion
on addtion.studentno=makingcredit.studentno
where addcredit!=addtion.amount

--更新素质类课程部分
update makingcredit
set coursecredit=coursecredit.amount
from makingcredit inner join (
select sum(courses.credits) amount,scores.studentno as studentno
from scores inner join courses on courses.courseno=scores.courseno
where (scores.courseno like '08%' or scores.courseno like '007%' or scores.[group] like 'G%') and (examscore>=60 or testscore in ('合格','及格','中等','优秀','良好')
or examscore2>=60 or testscore2 in ('合格','及格','中等','优秀','良好'))
group by scores.studentno ) as coursecredit on coursecredit.studentno=makingcredit.studentno
where coursecredit!=coursecredit.amount