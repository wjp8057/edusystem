SELECT ISNULL(SUM(学分), 0) AS xf
FROM (SELECT DISTINCT COURSES.COURSENO AS kh, COURSES.COURSENAME AS km, CREDITS AS 学分
	FROM R12 INNER JOIN COURSES ON COURSES.COURSENO = R12.COURSENO
	WHERE PROGRAMNO = :programno
		AND (EXISTS (SELECT *
				FROM SCORES
				WHERE STUDENTNO = :stone
					AND R12.COURSENO = SCORES.COURSENO
					AND (EXAMSCORE >= 60
						OR EXAMSCORE2 >= 60
						OR TESTSCORE IN (''优秀'', ''良好'', ''及格'', ''中等'', ''合格'')
						OR TESTSCORE2 IN (''优秀'', ''良好'', ''及格'', ''中等'', ''合格'')))
			OR EXISTS (SELECT *
				FROM SCORES INNER JOIN R33 ON R33.EQNO = SCORES.COURSENO
				WHERE STUDENTNO = :sttwo
					AND R12.COURSENO = R33.COURSENO
					AND (EXAMSCORE >= 60
						OR EXAMSCORE2 >= 60
						OR TESTSCORE IN (''优秀'', ''良好'', ''及格'', ''中等'', ''合格'')
						OR TESTSCORE2 IN (''优秀'', ''良好'', ''及格'', ''中等'', ''合格'')))
			OR EXISTS (SELECT *
				FROM SCORES INNER JOIN R33 ON R33.COURSENO = SCORES.COURSENO
				WHERE STUDENTNO = :stthree
					AND R12.COURSENO = R33.EQNO
					AND (EXAMSCORE >= 60
						OR EXAMSCORE2 >= 60
						OR TESTSCORE IN (''优秀'', ''良好'', ''及格'', ''中等'', ''合格'')
						OR TESTSCORE2 IN (''优秀'', ''良好'', ''及格'', ''中等'', ''合格''))))
	) TEMP