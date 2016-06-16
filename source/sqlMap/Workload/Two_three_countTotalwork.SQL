select count(*) AS ROWS from(select row_number() over(order by T.Courseno) as row,T.Totalwork_id,T.Courseno as kh,T.W_hours as mzks,T.Standard as bzb,T.Coursename as km,COURSES.CREDITS as xf,T.Estimate as yjrs,T.Attendent as renshu,COURSEPLAN.WEEKS,COURSEAPPROACHES.VALUE as xklx,COURSETYPEOPTIONS.VALUE AS kclx,
COURSES.HOURS,T.Standard,T.Classname as skbj,T.teachername as jsxm,t.teacherno,t.school,RTRIM(standard_class.code) as code from totalwork as T left join COURSEPLAN on
(RTRIM(T.Courseno)+RTRIM(T.group)=COURSEPLAN.COURSENO+COURSEPLAN.[GROUP] and T.YEAR=COURSEPLAN.YEAR AND T.TERM=COURSEPLAN.TERM and COURSEPLAN.CLASSNO=T.classno)
left join COURSES ON COURSEPLAN.COURSENO=COURSES.COURSENO
LEFT JOIN COURSETYPEOPTIONS ON COURSES.TYPE=COURSETYPEOPTIONS.NAME
LEFT JOIN COURSEAPPROACHES ON COURSEAPPROACHES.NAME=COURSEPLAN.COURSETYPE
left join standard_class on standard_class.course_id=COURSEPLAN.RECNO
where T.YEAR=:YEAR and T.TERM=:TERM and ISNULL(T.classno,'') like :CLASSNO and ISNULL(T.courseno,'') like :COURSENO and ISNULL(T.teacherno,'') LIKE :TEACHERNO
and ISNULL(standard_class.code,'') LIKE :CODE )as b