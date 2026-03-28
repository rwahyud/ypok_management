-- MySQL compatibility helpers for PostgreSQL (Supabase)
-- Run this once in Supabase SQL Editor before using the app.

create or replace function month(ts timestamp)
returns integer
language sql
immutable
as $$
  select extract(month from ts)::integer;
$$;

create or replace function month(ts date)
returns integer
language sql
immutable
as $$
  select extract(month from ts)::integer;
$$;

create or replace function year(ts timestamp)
returns integer
language sql
immutable
as $$
  select extract(year from ts)::integer;
$$;

create or replace function year(ts date)
returns integer
language sql
immutable
as $$
  select extract(year from ts)::integer;
$$;

create or replace function curdate()
returns date
language sql
stable
as $$
  select current_date;
$$;

create or replace function date_format(ts timestamp, fmt text)
returns text
language sql
immutable
as $$
  select to_char(
    ts,
    replace(
      replace(
        replace(fmt, '%Y', 'YYYY'),
      '%m', 'MM'),
    '%d', 'DD')
  );
$$;

create or replace function date_format(ts date, fmt text)
returns text
language sql
immutable
as $$
  select to_char(
    ts,
    replace(
      replace(
        replace(fmt, '%Y', 'YYYY'),
      '%m', 'MM'),
    '%d', 'DD')
  );
$$;
