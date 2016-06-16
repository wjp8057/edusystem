select 学号 as F1,讲座 as F2, 视频 as F3, 心理测评 as F4, 职业测评 as F5, 生涯规划 as F6, 学习心得 as F7,
 室内集训 as F8, 室外集训 as F9, 养成训练 as F10, 社会实践 as F11, 加分项 as F12, 减分项 as F13, 总分 as F14
 from Vacation where 学号=:STUDENTNO