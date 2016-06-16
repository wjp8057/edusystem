select * from(select row_number() over(order by 课号) as row,课号 as kh,课程名称 as kcmc,修课方式 as xkfs,人数 as rs,
case[状态] when 0 then '开放' else '锁定' end as zt,'' as SL
from
(
select makeup.courseno as 课号,courses.coursename as 课程名称,'补考' as 修课方式,count(*) as 人数,isnull(sum(lock),0) as 状态
from makeup inner join courses on courses.courseno=makeup.courseno
where makeup.year=:year and makeup.term=:term and courses.school=:school
and makeup.courseno like :courseno
group by makeup.courseno,courses.coursename) as temp
)as b where b.row between :start and :end