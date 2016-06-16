update toalwork set jiaozhengxishu=1 where teacherno exists (
  select 1 from teachers where school='10'
)
update work set CFXS where teacherno exists (
  select 1 from teachers where school='10'
)