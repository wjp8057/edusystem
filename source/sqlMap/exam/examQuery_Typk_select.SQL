select * from(select
row_number() over(order by dbo.getOne(b.kh)) as row,
dbo.getOne(b.kh) as kh,
dbo.getOne(b.kcmc) as kcmc,
dbo.getOne(b.exam) as exam,
dbo.getOne(b.examtype) as examtype,
dbo.getOne(b.kkxy) as kkxy,
dbo.getOne(b.recno) as recno,
dbo.GROUP_CONCAT_MERGE(b.bj,'/') as bj,
sum(b.rs) as rs from
(
SELECT distinct scheduleplan.courseno+scheduleplan.[group] AS kh,
courses.coursename as kcmc,
scheduleplan.recno as recno,
scheduleplan.exam,
examoptions.value as examtype,
schools.name kkxy,
rtrim(classes.classname) as bj,
scheduleplan.ATTENDENTS rs
FROM courses inner join scheduleplan on courses.courseno=scheduleplan.courseno
inner join courseplan on courseplan.courseno=scheduleplan.courseno and
courseplan.year=scheduleplan.year and courseplan.term=scheduleplan.term
and courseplan.[group]=scheduleplan.[group]
inner join examoptions on  examoptions.name=courseplan.examtype inner join schools on courses.school=schools.school
inner join classes on classes.classno=courseplan.classno
where courses.courseno=scheduleplan.courseno and
      scheduleplan.year=:year and
      scheduleplan.term=:term and
      courses.school like :school and
      CAST(scheduleplan.exam as char(1)) like :EXAMNO
) as b group by b.kh)as c where c.row between :start and :end