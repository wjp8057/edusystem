select count(ProgramNo) from Programs
where ProgramNo like :P_NO and ProgName like :P_NAME
and School like :SC
