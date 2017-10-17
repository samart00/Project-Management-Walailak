#include<stdio.h>
#include<stdlib.h>
#include<conio.h>
#include<string.h>
#include <windows.h>
using namespace std;
int main()
{
	system ("mongoimport --db wu-dev --collection user --drop --file user.json");
	system ("mongoimport --db wu-dev --collection category --drop --file category.json");
	system ("mongoimport --db wu-dev --collection team --drop --file team.json");
	system ("mongoimport --db wu-dev --collection project --drop --file project.json");
	system ("mongoimport --db wu-dev --collection department --drop --file department.json");
	system ("mongoimport --db wu-dev --collection task --drop --file task.json");
	system ("mongoimport --db wu-dev --collection auth_assignment --drop --file auth_assignment.json");
	system ("mongoimport --db wu-dev --collection auth_item --drop --file auth_item.json");
	system ("mongoimport --db wu-dev --collection policy --drop --file policy.json");
	printf("Please any key ...");
	getch();
}
