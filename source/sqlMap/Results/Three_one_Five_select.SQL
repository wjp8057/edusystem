select * from(select row_number() over(order by 课号) as row,课号 as kh,课程名称 as kcmc,修课方式 as xkfs,人数 as rs,
case[状态] when 0 then '开放' else '锁定' end as zt
from
(
select scores.courseno+scores.[group] as 课号,courses.coursename as 课程名称,'毕业免听' as 修课方式,count(*) as 人数,isnull(sum(lock),0) as 状态
from scores inner join courses on courses.courseno=scores.courseno
where scores.year=:year and scores.term=:term and courses.school=:school and scores.[group]='BY'
and scores.courseno+scores.[group] like :courseno
group by scores.courseno+scores.[group],courses.coursename) as temp

)as b where b.row between :start and :end