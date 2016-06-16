 select 
 courses.courseno as COURSENO,
 courses.coursename as COURSENAME,
 SCHOOLS.NAME as SCHOOL,
 courses.CREDITS as CREDITS,
 courses.total as TOTAL,
 courses.hours as HOURS,
 courses.lhours as Lhours,
 courses.experiments as EXPERIMENTS,
 courses.computing as COMPUTING,
 courses.shours as SHOURS,
 courses.khours as KHOURS,
 courses.zhours as ZHOURS,
 courses.limit as [Limit],
 courses.Quarter as QUARTER,
 COURSETYPEOPTIONS.[VALUE] as [TYPE],
 COURSETYPEOPTIONS2.[VALUE] as [TYPE2],
 courses.SYLLABUS as SYLLABUS,
 courses.rem as REM,
 courses.课程介绍 as INTRODUCE
 from courses
LEFT OUTER JOIN SCHOOLS ON SCHOOLS.SCHOOL = COURSES.SCHOOL
LEFT OUTER JOIN COURSETYPEOPTIONS ON COURSETYPEOPTIONS.NAME = courses.type
LEFT OUTER JOIN COURSETYPEOPTIONS2 ON COURSETYPEOPTIONS2.NAME = courses.type2
 where courseno = :courseno;