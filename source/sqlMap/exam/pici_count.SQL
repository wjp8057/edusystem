select count(*) as ROWS from (select row_number() over(order by b.kh) as row,b.* from(
SELECT distinct scheduleplan.courseno+scheduleplan.[group] AS kh,
courses.coursename as kcmc,
scheduleplan.recno,
scheduleplan.exam,
examoptions.value as examtype,
schools.name kkxy,
rtrim(classes.classname)+'/' as bj,
rtrim(classes.classno) as classno,
scheduleplan.ATTENDENTS rs
FROM
testcourse
 inner join scheduleplan  on testcourse.courseno=rtrim(scheduleplan.courseno)+rtrim(scheduleplan.[group])
 inner join courses on courses.courseno=scheduleplan.courseno
inner join courseplan on courseplan.courseno=scheduleplan.courseno and
courseplan.year=scheduleplan.year and courseplan.term=scheduleplan.term
and courseplan.[group]=scheduleplan.[group]
inner join examoptions on  examoptions.name=courseplan.examtype inner join schools on courses.school=schools.school
inner join classes on classes.classno=courseplan.classno
where courses.courseno=scheduleplan.courseno and
      scheduleplan.year=:year and
      scheduleplan.term=:term and
      classes.classno like :classno
) as b) as c
