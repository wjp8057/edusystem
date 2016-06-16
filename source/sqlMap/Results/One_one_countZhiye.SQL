select count(*) as ROWS from(select row_number() over(order by 学号)as row,学号 as xh,讲座 as jz,视频 as sp,
心理测评 as xlcp,生涯规划 as sygh,学习心得 as xxxd,室内集训 as snjx,室外集训 as swjx,
养成训练 as ycxl,社会实践 as shsj,加分项 as jfx,减分项 as jianfx,总分 as zf
 from Vacation
where 学号=:STUDENTNO
) as b