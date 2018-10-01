use schedule;

select
    *
from
    playerGameStats
limit 10;

select
    *
from
    game
limit 10;

select
    *
from
    flight
limit 10;

select
    data.divisionName,
    data.gameId,
    data.homeTeamScore,
    data.visitingTeamScore,
    data.homeTeamPlayerGoals,
    data.visitingTeamPlayerGoals
from
(
select
    g.id as gameId,
    g.homeTeamScore,
    g.visitingTeamScore,
    d.name as divisionName,
    sum(case when p.teamId = g.homeTeamId then p.goals else 0 end) as homeTeamPlayerGoals,
    sum(case when p.teamId = g.visitingTeamId then p.goals else 0 end) as visitingTeamPlayerGoals
from
    schedule as s
    join division as d on
      d.id = s.divisionId
      and d.scoringTracked = 1
    join flight as f on
        f.scheduleId = s.id
    join game as g on
        g.flightId = f.id
        and g.homeTeamScore is not null
    join playerGameStats as p on
        p.gameId = g.id
where
    s.name like '2018%'
group by
    1,2,3,4
) as data
where
    data.homeTeamScore !=data.homeTeamPlayerGoals
    or data.visitingTeamScore != data.visitingTeamPlayerGoals
order by
    1;

select
    concat("update game set homeTeamScore = ",
        data.homeTeamPlayerGoals,
        ", visitingTeamScore = ",
        data.visitingTeamPlayerGoals,
        " where id = ",
        data.gameId,
        ";") as upd
from
(
select
    g.id as gameId,
    g.homeTeamScore,
    g.visitingTeamScore,
    d.name as divisionName,
    sum(case when p.teamId = g.homeTeamId then p.goals else 0 end) as homeTeamPlayerGoals,
    sum(case when p.teamId = g.visitingTeamId then p.goals else 0 end) as visitingTeamPlayerGoals
from
    schedule as s
    join division as d on
      d.id = s.divisionId
      and d.scoringTracked = 1
    join flight as f on
        f.scheduleId = s.id
    join game as g on
        g.flightId = f.id
        and g.homeTeamScore is not null
    join playerGameStats as p on
        p.gameId = g.id
where
    s.name like '2018%'
group by
    1,2,3,4
) as data
where
    data.homeTeamScore !=data.homeTeamPlayerGoals
    or data.visitingTeamScore != data.visitingTeamPlayerGoals
order by
    1;

update game set homeTeamScore = 0, visitingTeamScore = 11 where id = 6041;
update game set homeTeamScore = 0, visitingTeamScore = 5 where id = 6038;
update game set homeTeamScore = 0, visitingTeamScore = 7 where id = 6134;
update game set homeTeamScore = 0, visitingTeamScore = 9 where id = 6130;
update game set homeTeamScore = 1, visitingTeamScore = 2 where id = 6067;
update game set homeTeamScore = 1, visitingTeamScore = 6 where id = 6149;
update game set homeTeamScore = 1, visitingTeamScore = 8 where id = 6147;
update game set homeTeamScore = 10, visitingTeamScore = 6 where id = 6054;
update game set homeTeamScore = 12, visitingTeamScore = 3 where id = 6044;
update game set homeTeamScore = 2, visitingTeamScore = 1 where id = 6131;
update game set homeTeamScore = 2, visitingTeamScore = 3 where id = 6053;
update game set homeTeamScore = 3, visitingTeamScore = 0 where id = 6143;
update game set homeTeamScore = 3, visitingTeamScore = 2 where id = 6122;
update game set homeTeamScore = 4, visitingTeamScore = 2 where id = 6125;
update game set homeTeamScore = 4, visitingTeamScore = 3 where id = 6047;
update game set homeTeamScore = 5, visitingTeamScore = 0 where id = 6154;
update game set homeTeamScore = 5, visitingTeamScore = 1 where id = 6035;
update game set homeTeamScore = 6, visitingTeamScore = 0 where id = 6057;
update game set homeTeamScore = 6, visitingTeamScore = 3 where id = 6050;
update game set homeTeamScore = 6, visitingTeamScore = 4 where id = 6068;
