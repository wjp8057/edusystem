select * from(SELECT row_number() over(ORDER BY 学年,学期,课号) as row,SCHOOLS.NAME AS kkxy, 学年 as xn,学期 as xq,PLANTYPE, TESTTYPE, LEFT(课号, 7) AS kh,
      课名 as km, 学分 as xf, 修课方式 as xkfs, 考核方式 as khfs, 考试成绩 as kscj, 考查成绩 as kccj, 补考考试 as bkks, 补考考查 as bkkc
FROM l_VIEWSCORE INNER JOIN
      SCHOOLS ON L_VIEWSCORE.开课学院 = SCHOOLS.SCHOOL
WHERE STUDENTNO =:STUDENTNO AND 考试成绩 < 60 AND 考查成绩 NOT IN ('及格',
      '合格', '中等', '优秀', '良好') AND (补考考试 < 60 OR
      补考考试 IS NULL) AND (补考考查 NOT IN ('及格', '合格', '中等', '优秀', '良好') OR
      补考考查 IS NULL) AND NOT EXISTS
          (SELECT *
         FROM L_VIEWSCORE AS TEMP
         WHERE TEMP .STUDENTNO = L_VIEWSCORE.STUDENTNO AND
               substring(TEMP .课号, 1, 7) = substring(l_viewscore.课号, 1, 7) AND
               (考试成绩 >= 60 OR
               考查成绩 IN ('及格', '合格', '中等', '优秀', '良好') OR
               补考考试 >= 60 OR
               补考考查 IN ('及格', '合格', '中等', '优秀', '良好')))

) as b where b.row between :start and :end