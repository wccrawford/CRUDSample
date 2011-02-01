create database contacts;

use contacts;

create table contacts (
	id serial not null,
	first_name text not null,
	last_name text not null,
	phone text not null,
	address text not null,
	address2 text,
	city text not null,
	state text,
	postal text,
	country text not null
	);

create user contacts@localhost;

set password for contacts@localhost = password('contacts');

grant select,insert,update,delete on contacts to contacts@localhost;

