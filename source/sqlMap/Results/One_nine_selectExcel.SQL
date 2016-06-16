SELECT row_number() over(ORDER BY L_VIEWSCORE.STUDENTNO) as row,课号 as kh,课名 as km,学分 as xf,
L_VIEWSCORE.STUDENTNO AS xh,STUDENTS.NAME AS xm,修课方式 as xkfs,考试成绩 as kscj,考查成绩 as kccj,补考考试 as bkks,
补考考查 as bkkc,积点分 as jdf,重修 as cx,听课方式 as tkfs,考试备注 as ksbz
FROM l_VIEWSCORE,STUDENTS
WHERE 课号  like   :COURSENO AND STUDENTS.STUDENTNO=L_VIEWSCORE.STUDENTNO
and 学年=:YEAR and 学期=:TERM and STUDENTS.STUDENTNO like :STUDENTNO
