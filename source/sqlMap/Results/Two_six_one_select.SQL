select * from(SELECT  row_number() over(ORDER BY 计算机统考成绩.学院, 计算机统考成绩.年级) as row,
dbo.getOne(isnull(计算机统考成绩.学院,0)) as xy,dbo.getOne(isnull(计算机统考成绩.年级,0)) as nj,dbo.getOne(isnull(ls_ksrs.考试人数,0)) as ksrs,
      dbo.getOne(isnull(ls_yxrs.优秀人数,0)) AS yxrs, dbo.getOne(isnull(ls_hgrs.合格人数,0)) as hgrs,dbo.getOne(isnull(ls_bhgrs.不合格人数,0)) as bhgrs
      
FROM 计算机统考成绩 LEFT OUTER JOIN
          (SELECT 计算机统考成绩.学院, 计算机统考成绩.年级, COUNT(*) 
               AS 考试人数
         FROM 计算机统考成绩
         WHERE 计算机统考成绩.年 =:YONE AND 计算机统考成绩.月份 =:TONE and 计算机统考成绩.等级 like'一级%'
         GROUP BY 计算机统考成绩.学院, 计算机统考成绩.年级) AS Ls_ksrs ON 
      ls_ksrs.学院 = 计算机统考成绩.学院 AND 
      ls_ksrs.年级 = 计算机统考成绩.年级 LEFT OUTER JOIN
          (SELECT 计算机统考成绩.学院, 计算机统考成绩.年级, COUNT(*) 
               AS 优秀人数
         FROM 计算机统考成绩
         WHERE 计算机统考成绩.年 =:YTWO AND 计算机统考成绩.月份 =:TTWO AND
               计算机统考成绩.成绩 >= 85 and 计算机统考成绩.等级 like'一级%'
         GROUP BY 计算机统考成绩.学院, 计算机统考成绩.年级) AS Ls_yxrs ON 
      ls_yxrs.学院 = 计算机统考成绩.学院 AND 
      ls_yxrs.年级 = 计算机统考成绩.年级 LEFT OUTER JOIN
          (SELECT 计算机统考成绩.学院, 计算机统考成绩.年级, COUNT(*) 
               AS 合格人数
         FROM 计算机统考成绩
         WHERE 计算机统考成绩.年 =:YTHREE AND 计算机统考成绩.月份 =:TTHREE AND
               计算机统考成绩.成绩 >= 60 and 计算机统考成绩.等级 like'一级%'
         GROUP BY 计算机统考成绩.学院, 计算机统考成绩.年级) AS Ls_hgrs ON 
      ls_hgrs.学院 = 计算机统考成绩.学院 AND 
      ls_hgrs.年级 = 计算机统考成绩.年级 LEFT OUTER JOIN
          (SELECT 计算机统考成绩.学院, 计算机统考成绩.年级, COUNT(*) 
               AS 不合格人数
         FROM 计算机统考成绩
         WHERE 计算机统考成绩.年 =:YFOUR AND 计算机统考成绩.月份 =:TFOUR AND
               计算机统考成绩.成绩 < 60 and 计算机统考成绩.等级 like'一级%'
         GROUP BY 计算机统考成绩.学院, 计算机统考成绩.年级) AS Ls_bhgrs ON 
      ls_bhgrs.学院 = 计算机统考成绩.学院 AND 
      ls_bhgrs.年级 = 计算机统考成绩.年级
WHERE 计算机统考成绩.年 =:YFIVE AND 计算机统考成绩.月份 =:TFIVE and 计算机统考成绩.等级 like'一级%'
group by 计算机统考成绩.学院, 计算机统考成绩.年级, ls_ksrs.考试人数, 
      ls_yxrs.优秀人数 , ls_hgrs.合格人数, ls_bhgrs.不合格人数
)as b