use schedule;

--
-- Swap pools and games for two teams
--
select
    *
from
    team
where
    nameId in ("B2009-8-14", "B2009-8-21");

select
    *
from
    game
where
    poolId in (61, 62)
    and (homeTeamId in (50, 57) or visitingTeamId in (50, 57));

select * from game where homeTeamId       = 50 and poolId = 61;
select * from game where visitingTeamId   = 50 and poolId = 61;
select * from game where homeTeamId       = 57 and poolId = 62;
select * from game where visitingTeamId   = 57 and poolId = 62;

update team set poolId = 62 where id = 50;
update team set poolId = 61 where id = 57;

update game set homeTeamId      = 57 where homeTeamId       = 50 and poolId = 61;
update game set visitingTeamId  = 57 where visitingTeamId   = 50 and poolId = 61;

update game set homeTeamId      = 50 where homeTeamId       = 57 and poolId = 62;
update game set visitingTeamId  = 50 where visitingTeamId   = 57 and poolId = 62;

