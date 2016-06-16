SELECT L_VIEWSCORE.studentno, 课号 as kh, 课名 as km, 学分 as xf, 修课方式 as xkfs, 学年 as year,  学期 as term, 考试成绩 as kscj, 考查成绩 as kccj,
      补考考试 as bkks,补考考查 as bkkc
FROM L_VIEWSCORE,STUDENTS
WHERE L_VIEWSCORE.studentno LIKE :STUDENTNO  and STUDENTS.classNO  LIKE :CLASSNO AND STUDENTS.STUDENTNO=L_VIEWSCORE.STUDENTNO
ORDER BY L_VIEWSCORE.studentno, 学年, 学期, 课号