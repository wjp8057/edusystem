select * from(select row_number() over(order by b.kh) as row,b.* from(SELECT DISTINCT
      scores.courseno + scores.[group] kh, ls_km.km AS km,
      isnull(ls_xkrs.xkrs,0) xkrs, isnull(ls.yxrs,0) yxrs,isnull(ls_lhrs.lhrs,0) lhrs,
      isnull(ls_zdrs.zdrs,0) zdrs, isnull(ls_jgrs.jgrs,0) jgrs, isnull(ls_bjgrs.bjgrs,0) bjg,
      isnull(ls_qkrs.qkrs,0) qkrs
FROM SCORES LEFT OUTER JOIN
          (SELECT courses.courseno AS kh, courses.coursename km
         FROM courses) AS ls_km ON scores.courseno = ls_km.kh LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS xkrs
         FROM SCORES
	WHERE scores.courseno+scores.[group] LIKE :CONE and  scores.year=:YONE and scores.term=:TONE
         GROUP BY scores.courseno + scores.[group]) AS Ls_xkrs ON
      scores.courseno + scores.[group] = ls_xkrs.xx LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS yxrs
         FROM SCORES
         WHERE scores.courseno+scores.[group] LIKE :CTWO and
		 scores.year=:YTWO and scores.term=:TTWO and
               (scores.testscore = '优秀' OR
               scores.examscore >= 90)
         GROUP BY scores.courseno + scores.[group]) AS ls ON
      scores.courseno + scores.[group] = ls.xx LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS lhrs
         FROM SCORES
         WHERE scores.courseno+scores.[group] LIKE :CTHREE and
 scores.year=:YTHREE and scores.term=:TTHREE and (scores.testscore = '良好' OR
               (scores.examscore >= 80 AND scores.examscore < 90))
         GROUP BY scores.courseno + scores.[group]) AS ls_lhrs ON
      scores.courseno + scores.[group] = ls_lhrs.xx LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS zdrs
         FROM SCORES
         WHERE scores.courseno+scores.[group] LIKE :CFOUR and scores.year=:YFOUR and scores.term=:TFOUR and  (scores.testscore = '中等' OR
               (scores.examscore >= 70 AND scores.examscore < 80))
         GROUP BY scores.courseno + scores.[group]) AS ls_zdrs ON
      scores.courseno + scores.[group] = ls_zdrs.xx LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS jgrs
         FROM SCORES
         WHERE scores.courseno+scores.[group] LIKE :CFIVE and
scores.year=:YFIVE and scores.term=:TFIVE and (scores.testscore = '及格' OR
               scores.testscore = '合格' OR
               (scores.examscore >= 60 AND scores.examscore < 70))
         GROUP BY scores.courseno + scores.[group]) AS ls_jgrs ON
      scores.courseno + scores.[group] = ls_jgrs.xx LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS bjgrs
         FROM SCORES
         WHERE  scores.courseno+scores.[group] LIKE :CSIX and
scores.year=:YSIX and scores.term=:TSIX and
               ((scores.testscore = '不及格' OR
               scores.testscore = '不合格' OR
               scores.testscore = '作弊') OR
               (scores.examscore > 0 AND scores.examscore < 60) OR
               (scores.examscore = 0 AND scores.testscore = ''))
         GROUP BY scores.courseno + scores.[group]) AS ls_bjgrs ON
      scores.courseno + scores.[group] = ls_bjgrs.xx LEFT OUTER JOIN
          (SELECT scores.courseno + scores.[group] xx, COUNT(scores.courseno)
               AS qkrs
         FROM SCORES
         WHERE scores.courseno+scores.[group] LIKE :CSEVEN and
scores.testscore = '缺考' and  scores.year=:YSEVEN and scores.term=:TSEVEN
         GROUP BY scores.courseno + scores.[group]) AS ls_qkrs ON
      scores.courseno + scores.[group] = ls_qkrs.xx
WHERE scores.courseno+scores.[group] LIKE :CEIGHT and scores.year=:YEIGHT and scores.term=:TEIGHT
) as b )as c where c.row between :start and :end
