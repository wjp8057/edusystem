select count(*) as ROWS from(SELECT row_number() over(ORDER BY classes.school,classes.classno,学号,学年,学期) as row,[L_view_不及格的必修课].学年 AS xn,
[L_view_不及格的必修课].学期 as xq,
[L_view_不及格的必修课].课号 as kh,
[L_view_不及格的必修课].课程名称 as ckmc,
[L_view_不及格的必修课].考试成绩 as kscj,
[L_view_不及格的必修课].考查成绩 as kccj,
[L_view_不及格的必修课].重修成绩 as cxcj,
[L_view_不及格的必修课].选课性质 as xkxz,
[L_view_不及格的必修课].学号 as xh,
[L_view_不及格的必修课].姓名 as xm,
classes.classno bh,classes.school xydh
FROM [L_view_不及格的必修课],students,classes
WHERE 学号 LIKE :STUDENTNO and students.studentno=[L_view_不及格的必修课].学号
and students.classno=classes.classno and classes.school like :SCHOOL
and (students.studentno like '00%' OR
      students.studentno LIKE '013%' OR
      students.studentno LIKE '9%' OR
      students.studentno LIKE '022%' )  and ( status<>'J' and status<>'E')  and 课号 not like '040t1%'
) as b