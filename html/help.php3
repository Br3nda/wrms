<?php
  $title = "Help!";
  include("apms-header.php3");
?>
<A HREF="#overview">Overview</A>&nbsp;&nbsp;|
<A HREF="#screens">Screens</A>&nbsp;&nbsp;|
<A HREF="#miscellaneous">Miscellaneous</A>&nbsp;&nbsp;|
<A HREF="#faq">Frequently Asked Questions</A>
<BR>
<HR>
<A NAME="overview"><H2>Overview</H2></A>
<P>Catalyst's Work Request Management System is designed to help our clients monitor our progress in responding to requests for our services.</P>
<P>In general the system is most useful for:<UL>
<LI>Problem Resolution</LI>
<LI>Ad-hoc Requests</LI>
<LI>Enhancements requiring a quote</LI>
<LI>Program Maintenance</LI>
<LI>Requests for Assistance</LI>
<LI>Network support requests</LI>
</UL>
<P>On the other hand, there are some situations where the system is not particularly well-suited, particularly for those kinds of work where there needs to be a high level of personal interaction with the client:<UL>
<LI>Analysis and specification work</LI>
<LI>Business Consulting</LI>
<LI>System Design and Development</LI>
</UL>

<BR><HR>
<A NAME=layout><H4>Layout of Screens</H4></A>
<P>The screen layout follows a generally consistent behaviour.  The top of each screen is a 'header' area identifying the function / and information being access / displayed.</P>
<P>At the bottom of each scren is a one or two line menu of general options.  For specific ('view') screens this may have several options related to the particular record being viewed.</P>
<P>The central part of each screen is either a tabulated 'listing' of many records, or a detailed 'view' particular record and associated information.</P>
<H5>List Screens</H5>
<P>These generally show many records in a table.  The first line of the table should identify the contents of each of the columns in each of the following rows.  Each row in the list may have hyperlinks to view the detailed record, or perform some other related action.</P>
<P>At the top or bottom of the list there may be a small form which allows you to narrow or broaden the criteria for selection of the records to appear in the listing.</P>
<H5>View Screens</H5>
<P>View screens will show all available detail about a specific record, usually selected from a listing screen.  
<H5>Combination Screens</H5>
<P>A 'view' screen is often combined with listings of associated records.</P>
<P>The listings on such a screen will generally be similar to those on a list-only screen, but are unlikely to have forms allowing selection of the displayed records, since these are generally selected to those relevant to the 'viewed' record.</P>
<P>In some cases the 'viewed' record will be made available for modification in this screen as well.  In the case of the WRMS that usually means that you are the maintainer of the system concerned, the administrative user for a particular organisation, or the person making a particular request.</P>
<BR><BR><HR>
<A NAME=screens><H2>Screens</H2></A>
<A HREF=#manual>Manual</A>&nbsp;&nbsp;|
<A HREF=#list-requests>List Requests</A>&nbsp;&nbsp;|
<A HREF=#view-request>View Request</A>&nbsp;&nbsp;|
<A HREF=#view-update>View Update</A>&nbsp;&nbsp;|
<A HREF=#manual>Manual</A>&nbsp;&nbsp;|

<BR><HR>
<A NAME=manual><H4>Manual</H4></A>
<P>The manual for Capital APMS, Catalyst's property and accounting management system, is available on-line.</P>
<P>The on-line manual contains sections for beginning users, advanced users, administrators, as well as a collection of answers to users' frequently asked questions.</P>

<BR><HR>
<A NAME=list-requests><H4>List Requests</H4></A>
<P>This is probably the most important screen in the WRMS.  It lists requests according to various criteria which may be adjusted through the form at the bottom of the page.</P>
<P>The default is to list requests in the same way that you last listed them.  The first time this will default to listing those requests which have been asked for by people who work for your organisation.</P>
<P>Each request listed includes a link to the full details of the request.  Click on <A HREF="#view-request">this link</A> to display the full details of the request.</P>

<BR><HR>
<A NAME=view-request><H4>View Request</H4></A>
<P>Another key screen, this shows all possible information related to a particular work request, including:
<UL><LI>Basic request details</LI>
<LI>Any '<A HREF=#update>update</A>' files pertaining to the request</LI>
<LI>Details of any quotations for the request</LI>
<LI>People who have been allocated to the problem</LI>
<LI>Timesheet entries related to the request</LI>
<LI>People who are registered as '<A HREF=#interested>interested</A>' in the request</LI>
<LI>Any notes, which may have been added by people reviewing the requests.</LI>
<LI>Changes of status, who they were made by and when</LI>
</UL>

<BR><HR>
<A NAME=view-update><H4>View Update</H4></A>
<P>Where applicable, updates which address specific work requests may be downloaded from the WRMS site and applied against internal systems.</P>
<P>This screen displays the details of system updates</P>

<BR><BR><HR>
<A NAME=miscellaneous><H2>Miscellaneous</H2></A>
<P>All those little bist and pieces of information that don't really fit anywhere else</P>
<BR><HR>
<A NAME=interested><H4>Registering 'Interest' in a Request</H4></A>
<P>The <A HREF=#view-request>View Requests</A> screen has a button at the top right which allows users to identify themselves as 'interested' in a particular request.</P>
<P>Some people are automatically identified as 'interested' when a request is added, including:
<UL><LI>The person responsible for the system which the request pertains to</LI>
<LI>The person identified as the contact point for the organisation making the request</LI>
<LI>(Optionally) the person making the request</LI>
</UL>
<P>Being 'interested' in a request means that you will receive e-mail messages whenever the status of the request changes, or when someone adds a note, quote, staff allocation or otherwise changes the request in some way</P>
<P>If you uncheck the 'Keep me updated on this request' option while registering a work request you will <I>not</I> be updated on changes to the request.  This might be the case when you see some very minor problem but it's not important enough to really care about.</P>

<BR><HR>
<A NAME=update><H4>System Updates</H4></A>
<P>System updates can be downloaded from the WRMS and applied against your internal systems if they support this.  Systems currently supporting this method of update include:
<UL>
<LI><A HREF="http://www.cat-it.co.nz/capital/">Capital APMS</A> - Catalyst's Accounting and Property Management System</LI>
</UL>
<P>When supported, system updates will allow for most kinds of problems to be resolved through a dcwnloaded fix.  Effects of system updates are also identified, and associated with specific work requests.</P>

<BR><HR>
<A NAME=me><H4>Changing My Details</H4></A>
<P>You can change most of your own user details by clicking on the your name at the bottom left of most screens.</P>

<BR><BR><HR>
<A NAME=faq><H2>Frequently Asked Questions</H2></A>
<P>This section contains answers to questions which have been asked by people from time to time.</P>
<P>Unfortunately the system is so new as yet that nobody has really asked me any questions about it!  If you have a question which you think you should be able to find the answer to here, why not <A HREF=new-request.php3>put in a request</A>!</P>


<BR><BR>
<?php
  include("apms-footer.php3");
  echo "<P><BR> <BR><BR><BR><BR> <BR><BR><BR><BR> <BR><BR><BR><BR> <BR><BR><BR><BR><BR> <BR><BR><BR><BR> <BR><BR><BR><BR><BR> <BR><BR><BR><BR> <BR><BR><BR>";
  phpinfo();
  echo "</P>";
?>
