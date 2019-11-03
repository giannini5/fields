-- Add 15 minutes to game start time
use schedule;
set @division = '14U';
select id into @gameDate1 from gameDate where day = '2019-11-09';
select id into @gameDate2 from gameDate where day = '2019-11-10';

/*
select
    gt.gameId
from
    gameDate as gd
    join gameTime as gt on
        gt.gameDateId = gd.id
    join game as g on
        g.id = gt.gameId
    join schedule as s on
        s.id = g.scheduleId
    join division as d on
        d.id = s.divisionId
        and d.name = @division
where
    day between @startDay and @endDay;
*/

update gameTime
set 
    actualStartTime = case when @division in ('10U', '12U') and startTime = '09:15:00' then '09:30:00'
                           when @division in ('10U', '12U') and startTime = '10:30:00' then '11:00:00'
                           when @division in ('10U', '12U') and startTime = '11:45:00' then '12:30:00'
                           when @division in ('10U', '12U') and startTime = '13:00:00' then '14:00:00'
                           when @division in ('10U', '12U') and startTime = '14:15:00' then '15:30:00'
                           when @division in ('10U', '12U') and startTime = '15:30:00' then '17:00:00'
                           
                           when @division = '14U' and startTime = '09:45:00' then '10:00:00'
                           when @division = '14U' and startTime = '11:30:00' then '12:00:00'
                           when @division = '14U' and startTime = '13:15:00' then '14:00:00'
                           when @division = '14U' and startTime = '15:00:00' then '17:00:00'
                           end
where
    id in (
        select
            g.gameTimeId
        from
            game as g
        join schedule as s on
            s.id = g.scheduleId
        join division as d on
            d.id = s.divisionId
            and d.name = @division
            )
    -- and startTime = @currentStartTime
    and gameDateId in (@gameDate1, @gameDate2);