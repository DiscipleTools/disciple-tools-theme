## Source File Notes

Source `.csv` files come from the IMB and Joshua Project indexes of people groups.
Both include a reference key called `ROP3`, which links the table lists as much as
there is agreement. 

### International Mission Board - Global Research (IMB)
There are a number of people group publications on the IMB website ([People Groups Downloads Page](http://www.peoplegroups.org/258.aspx)).
We have selected the `People Groups (Data Only)` source link because it includes less
formatting to correct ( [Link](http://grd.imb.org/wp-content/uploads/documents/gsec-files/2018-06_People_Groups_data_only.xls)). 

We then do a simple `.xls` to `.csv` conversion and save the resulting `.csv` file to the `/dt-people-groups/csv/` folder.

Finally, rename the `.csv` file to `imb.csv`. 

Future year updates can use this same process and overwrite the `imb.csv` file.


## Joshua Project (JP)
Joshua project offers `All people groups by country` ([link](https://joshuaproject.net/resources/datasets/1)) on it's 
downloads page ([link](https://joshuaproject.net/resources/datasets)). This should
download a file named `AllPeoplesByCountry.csv`.

Remove the first two lines of this file because it comes with the title line
```
JOSHUA PROJECT PEOPLES DATA

ROG3,Ctry,PeopleID3,ROP3,PeopNameAcrossCountries,PeopNameInCountry,Population,.....
```

This places the column names at the top of the file with no empty space. Now 
the `.csv` file that is ready for installing into the Disciple Tools theme. 

Change the name from `AllPeoplesByCountry.csv` to `jp.csv` and update the file in the theme.


## Update History
- (08-13-2018) - Initial install. Data from 2018-06. `http://grd.imb.org/wp-content/uploads/documents/gsec-files/2018-06_People_Groups_data_only.xls`
- (08-13-2018) - Initial install. `https://joshuaproject.net/resources/datasets/1`