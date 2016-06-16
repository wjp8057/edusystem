select * from (SELECT row_number() over(order by 课号)as row,课号 as courseno,课名 as coursename,学分 xuefen,修课方式 AS StudyType,考核方式 AS astype,周学时 as zxs,周实验 as zsy ,周上机 as zsj,开设学年 AS xuenian,开设学期 AS xueqi,上课周次 AS WEEKS,课程类别 AS CourseType,考核层次 as cengci,教学大纲 as dagang
FROM VIEWR12
WHERE PROGRAMNO=:PROGRAMNO)as b where b.row between :start and :end
