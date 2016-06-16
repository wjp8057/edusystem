select count(*) as ROWS from(select row_number() over(order by year) as row,year xn,term xq,courses.courseno kh,courses.coursename km,courses.credits xf,courseapproaches.value xkfs,programs.progname jxjhm
from planscore inner join courses on courses.courseno=planscore.courseno inner join COURSEAPPROACHES on COURSEAPPROACHES.name=coursetype
inner join programs on programs.programno=planscore.programno
where studentno=:STUDENTNO
) as b