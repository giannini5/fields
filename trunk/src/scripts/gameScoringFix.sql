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
update game set homeTeamScore = 0, visitingTeamScore = 0 where id = 5509;
update game set homeTeamScore = 0, visitingTeamScore = 1 where id = 5513;
update game set homeTeamScore = 0, visitingTeamScore = 1 where id = 5651;
update game set homeTeamScore = 0, visitingTeamScore = 4 where id = 5488;
update game set homeTeamScore = 0, visitingTeamScore = 5 where id = 5416;
update game set homeTeamScore = 0, visitingTeamScore = 5 where id = 5467;
update game set homeTeamScore = 0, visitingTeamScore = 7 where id = 5413;
update game set homeTeamScore = 0, visitingTeamScore = 7 where id = 5479;
update game set homeTeamScore = 1, visitingTeamScore = 3 where id = 5437;
update game set homeTeamScore = 1, visitingTeamScore = 3 where id = 5470;
update game set homeTeamScore = 1, visitingTeamScore = 4 where id = 5428;
update game set homeTeamScore = 1, visitingTeamScore = 4 where id = 5472;
update game set homeTeamScore = 1, visitingTeamScore = 4 where id = 5506;
update game set homeTeamScore = 1, visitingTeamScore = 5 where id = 5500;
update game set homeTeamScore = 1, visitingTeamScore = 7 where id = 5443;
update game set homeTeamScore = 1, visitingTeamScore = 7 where id = 5449;
update game set homeTeamScore = 10, visitingTeamScore = 0 where id = 5508;
update game set homeTeamScore = 10, visitingTeamScore = 1 where id = 5448;
update game set homeTeamScore = 14, visitingTeamScore = 0 where id = 5412;
update game set homeTeamScore = 2, visitingTeamScore = 0 where id = 5515;
update game set homeTeamScore = 2, visitingTeamScore = 1 where id = 5433;
update game set homeTeamScore = 2, visitingTeamScore = 1 where id = 5455;
update game set homeTeamScore = 2, visitingTeamScore = 1 where id = 5491;
update game set homeTeamScore = 2, visitingTeamScore = 10 where id = 5436;
update game set homeTeamScore = 2, visitingTeamScore = 3 where id = 5512;
update game set homeTeamScore = 2, visitingTeamScore = 3 where id = 5524;
update game set homeTeamScore = 2, visitingTeamScore = 4 where id = 5497;
update game set homeTeamScore = 2, visitingTeamScore = 4 where id = 5525;
update game set homeTeamScore = 2, visitingTeamScore = 5 where id = 5494;
update game set homeTeamScore = 2, visitingTeamScore = 8 where id = 5424;
update game set homeTeamScore = 3, visitingTeamScore = 0 where id = 5505;
update game set homeTeamScore = 3, visitingTeamScore = 1 where id = 5425;
update game set homeTeamScore = 3, visitingTeamScore = 4 where id = 5434;
update game set homeTeamScore = 3, visitingTeamScore = 4 where id = 5496;
update game set homeTeamScore = 3, visitingTeamScore = 4 where id = 5503;
update game set homeTeamScore = 3, visitingTeamScore = 6 where id = 5464;
update game set homeTeamScore = 4, visitingTeamScore = 0 where id = 5452;
update game set homeTeamScore = 4, visitingTeamScore = 1 where id = 5484;
update game set homeTeamScore = 4, visitingTeamScore = 1 where id = 5518;
update game set homeTeamScore = 5, visitingTeamScore = 0 where id = 5489;
update game set homeTeamScore = 5, visitingTeamScore = 2 where id = 5477;
update game set homeTeamScore = 5, visitingTeamScore = 6 where id = 5473;
update game set homeTeamScore = 6, visitingTeamScore = 0 where id = 5485;
update game set homeTeamScore = 6, visitingTeamScore = 0 where id = 5521;
update game set homeTeamScore = 6, visitingTeamScore = 1 where id = 5461;
update game set homeTeamScore = 6, visitingTeamScore = 2 where id = 5419;
update game set homeTeamScore = 6, visitingTeamScore = 7 where id = 5446;
update game set homeTeamScore = 7, visitingTeamScore = 0 where id = 5417;
update game set homeTeamScore = 7, visitingTeamScore = 0 where id = 5441;
update game set homeTeamScore = 7, visitingTeamScore = 1 where id = 5431;
update game set homeTeamScore = 7, visitingTeamScore = 1 where id = 5440;
update game set homeTeamScore = 7, visitingTeamScore = 1 where id = 5453;
update game set homeTeamScore = 7, visitingTeamScore = 2 where id = 5422;
update game set homeTeamScore = 7, visitingTeamScore = 2 where id = 5501;
update game set homeTeamScore = 7, visitingTeamScore = 2 where id = 5527;
update game set homeTeamScore = 7, visitingTeamScore = 3 where id = 5476;
