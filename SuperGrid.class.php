<?php
/**
*	SuperGrid -  A customizable PHP data grid that makes displaying tabular data easy for the developer
*	Usage Example:
*		<code>
* 		<?php
* 			$file_db = new PDO('sqlite:Northwind.sqlite3');
*			$stmt = $file_db->query("SELECT * FROM Customers");
*			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
*			
*			$sg = new SuperGrid();
*			$sg->setCssClass("SuperGridCss");
*			$sg->SetData($result);
*			$sg->MarkAlternateRowsCss = true;
*			$sg->MarkColumnsCss = true;
*			$sg->DisplayColumnNamesInFooter = true;
*			$sg->hideColumn("Address");
*			$sg->hideColumn("CustomerID");
*			$sg->setColumnTitle("CompanyName", "Company Name");
*			$sg->display();
* 		?>
* 		</code>
*	@author Vishal Mody <me@vishalmody.net>
* 	@access public
*	@package SuperGrid
*/
class SuperGrid
{
	/**
     * Column names - used to track columns internally
     * @var array 
     * @access private
     */
	private $ColumnNames = array();
	/**
     * Column headers - what is displayed in the HTML table
     * @var array 
     * @access private
     */
	private $ColumnTitles = array();
	/**
     * Boolean values to indicate visibility of each column
     * @var array 
     * @access private
     */
	private $ColumnVisibility = array();
	/**
     * HTML ID for tracking individual instances of SuperGrid
     * @var string
     * @access private
     */
	private $GridId;
	/**
     * Data that is displayed in the SuperGrid
     * @var array 
     * @access private
     */
	private $GridData = array();
	/**
     * CSS class name that is assigned to the HTML table
     * @var array 
     * @access private
     */
	private $CssClass;
	/**
     * Number of columns in the SuperGrid
     * @var integer
     * @access private
     */
	private $ColumnCount;
	/**
     * Number of rows in the SuperGrid
     * @var integer
     * @access private
     */
	private $RowCount;
	/**
     * Indicate if the column headers should be displayed in the SuperGrid's footer.  Default is false
     * @var bool
     * @access public
     */
	public $DisplayColumnNamesInFooter;
	/**
     * Indicate if alternate rows should have different CSS formatting.  Default is false.  
     *	To get the effect, alternate rows are assigned "odd" and "even" CSS classes by default
     * @var bool
     * @access public
     */
	public $MarkAlternateRowsCss;
	/**
     * Indicate if columns should be marked with CSS classes.  Default is false
     *	Column CSS class name is set the same as the column's respective ColumnName value
     * @var bool 
     * @access public
     */
	public $MarkColumnsCss;
	/**
     * CSS class name for odd-numbered rows.  Default is "odd"
     * @var string 
     * @access public
     */
	public $OddRowCss;
	/**
     * CSS class name for even-numbered rows.  Default is "even"
     * @var array 
     * @access public
     */
	public $EvenRowCss;
	/**
	 * @param string $GridId HTML ID for SuperGrid.  If one is not provided, a random ID will be generated
	 * @return void
	 */	
	public function __construct($GridId = null)
	{
		if (!is_null($GridId))
		{
			$this->GridId = $GridId;
		}
		else
		{
			$this->GridId = "SuperGrid" . mt_rand();
		}
		
		$this->CssClass = null;
		$this->ColumnCount = 0;
		$this->RowCount = 0;
		$this->DisplayColumnNamesInFooter = false;
		$this->MarkAlternateRowsCss = false;
		$this->MarkColumnsCss = false;
		$this->OddRowCss = "odd";
		$this->EvenRowCss = "even";
	}
	/**
	 * Hides the specified column
	 * @param string $ColumnName Column Name
	 * @return void
	 */	
	public function hideColumn($ColumnName)
	{
		$this->ColumnVisibility[$this->getColumnIndex($ColumnName)] = false;
	}
	/**
	 * Shows the specified column
	 * @param string $ColumnName Column Name
	 * @return void
	 */	
	public function showColumn($ColumnName)
	{
		$this->ColumnVisibility[$this->getColumnIndex($ColumnName)] = true;
	}
	/**
	 * Indicates if a column is visible
	 * @param string $ColumnName Column Name
	 * @return bool True or false
	 */	
	public function isColumnVisible($ColumnName)
	{
		return $this->ColumnVisibility[$this->getColumnIndex($ColumnName)];
	}
	/**
	 * Gets the column index
	 *
	 * @param string $ColumnName Column Name
	 * @return int Column index
	 */	
	private function getColumnIndex($ColumnName)
	{
		$searchResult = array_search($ColumnName, $this->ColumnNames);
		
		if ($searchResult === false)
		{
			throw new Exception("Column " . $ColumnName . " not found");
		}
		
		return $searchResult;
	}
	/**
	 * Adds a new column to the internal data table
	 *
	 * @param string $ColumnName Column Name
	 * @param string $ColumnTitle Column title (what will be displayed in the HTML header)
	 * @param int $ColumnIndex Index value of the column [Optional]
	 * @return void
	 */	
	public function addColumn($ColumnName, $ColumnTitle, $ColumnIndex = -1)
	{
		if ($ColumnIndex == -1)
		{
			$this->ColumnNames[] = $ColumnName;
			$this->ColumnTitles[] = $ColumnTitle;
			$this->ColumnVisibility[] = true;
		}
		else
		{
			array_splice($this->ColumnNames, $ColumnIndex, 0, $ColumnName);
			array_splice($this->ColumnTitles, $ColumnIndex, 0, $ColumnTitle);
			array_splice($this->ColumnVisibility, $ColumnIndex, 0, $ColumnTitle);
		}
		
		for ($RowCounter = 0; $RowCounter < $this->RowCount; $RowCounter ++)
		{
			$this->GridData[$RowCounter][$ColumnName] = null;
		}
		$this->ColumnCount++;
	}
	/**
	 * Formats the content of the specified column.  
	 *
	 *		For example, 
	 *			$sg = new SuperGrid();
	 *			$sg->SetData($result);
	 *			$sg->formatColumn("Edit", "<button onclick=\"alert('#E-Mail#');\">Edit</button>", true)
	 *
	 * @param string $ColumnName Column name
	 * @param string $ColumnFormat Column format
	 * @return void
	 */	
	public function formatColumn($ColumnName, $ColumnFormat, $FormatEmptyColumns = true)
	{
		for ($RowCounter = 0; $RowCounter < count($this->GridData); $RowCounter ++)
		{
			$CurrentRow = &$this->GridData[$RowCounter];
			$CurrentRow[$ColumnName] = $ColumnFormat;

			foreach($this->ColumnNames as $ColumnNameSearchID => $ColumnNameSearch)
			{
				if ($ColumnName != $ColumnNameSearch)
				{
					$CurrentRow[$ColumnName] = str_replace("#" . trim($ColumnNameSearch) . "#", $CurrentRow[$ColumnNameSearch], $CurrentRow[$ColumnName]);
				}
			}
		}
	}
	/**
	 * Sets the header text that is displayed on screen
	 *
	 * @param string $ColumnName Column Name
	 * @param string $ColumnTitle Column header
	 * @return void
	 */	
	public function setColumnTitle($ColumnName, $ColumnTitle)
	{
		try
		{
			$this->ColumnTitles[array_search($ColumnName, $this->ColumnTitles)] = $ColumnTitle;
		}
		catch(exception $e)
		{
			print ($e->getMessage());
		}
	}
	/**
	 * Gets the SuperGrid's HTML ID
	 *
	 * @return string
	 */	
	public function getGridId()
	{
		return $this->GridId;
	}
	/**
	 * Sets the CSS class name for the SuperGrid
	 *
	 * @return void
	 */	
	public function setCssClass($CssClassName = null)
	{
		if (!is_null($CssClassName))
		{
			$this->CssClass = $CssClassName;
		}
	}
	/**
	 * Gets the CSS class name of the SuperGrid
	 *
	 * @return string CSS class name
	 */	
	public function getCssClass()
	{
		return $this->CssClass;
	}
	/**
	 * Binds data to the SuperGrid.  Data must be in the form of an array
	 *
	 * @param array $Data Grid data
	 * @return void
	 */	
	public function setData($Data)
	{
		try
		{
			if (!is_array($Data))
			{
				throw new Exception("SuperGrid data must be in the form of an array");
			}
			
			$this->RowCount = count($Data);
			
			if ($this->RowCount > 0)
			{
				// Determine column titles
				
				$this->ColumnCount = count($Data[0]);
				foreach($Data[0] as $ColTitle=> $RowData)
				{
					$this->ColumnNames[] = $ColTitle;
					$this->ColumnTitles[] = $ColTitle;
					$this->ColumnVisibility[] = true;
				}
			}
			else
			{
				$this->ColumnCount = 0;
			}
			
			$this->GridData = $Data;
		}
		catch(Exception $e)
		{
			print($e->getMessage());
		}
	}
	/**
	 * Dumps all the metadata and grid data
	 *
	 * @param bool $Verbose If set to true, all grid data is dumped along with the metadata
	 * @return void
	 */	
	public function dump($Verbose = false)
	{
		print("<dl class='SuperGridConfigDump'>");
		printf("<dt>Grid ID</dt><dd>%s</dd>\n", $this->GridId);
		printf("<dt>Row Count</dt><dd>%d</dd>\n", $this->RowCount);
		printf("<dt>Column Count</dt><dd>%d</dd>\n", $this->ColumnCount);
		printf("<dt>CSS Class</dt><dd>%s</dd>\n", is_null($this->CssClass) ? "N/A" : $this->CssClass);
		printf("<dt>Display Column Names in Footer</dt><dd>%s</dd>\n", ($this->DisplayColumnNamesInFooter) ? "Yes" : "No");
		printf("<dt>Mark Alternate Rows</dt><dd>%s</dd>\n", ($this->MarkAlternateRowsCss) ? "Yes" : "No");
		printf("<dt>Mark Column CSS</dt><dd>%s</dd>\n", ($this->MarkColumnsCss) ? "Yes" : "No");
		printf("<dt>Odd Row CSS</dt><dd>%s</dd>\n", $this->OddRowCss);
		printf("<dt>Event Row CSS</dt><dd>%s</dd>\n", $this->EvenRowCss);

		if ($Verbose)
		{
			print("<dt>Grid Columns</dt>");
			print("<dd><table><thead><tr><th>Index</th><th>Name</th><th>Title</th><th>Visible</th></tr></thead><tbody>");
			foreach($this->ColumnNames as $ColumnID=>$ColumnName)
			{
				print("<tr><td>" . $ColumnID . "</td><td>" . $ColumnName . "</td><td>" . $this->ColumnTitles[$ColumnID] . "</td><td>" . ($this->ColumnVisibility[$ColumnID] == true ? "Yes" : "No") . "</td></tr>");
			}
			print("</tbody></table></dd>");
			
			print("<dt>Grid Data</dt>");
			print("<dd>");
			var_dump($this->GridData);
			print("</dd>");
		}
		print("</dl>");		
	}
	/**
	 * Displays the SuperGrid in HTML
	 *
	 * @return void
	 */	
	public function display()
	{
		if ($this->ColumnCount == 0)
		{
			throw new Exception("SuperGrid does not contain any columns to display");
		}
		
		printf("<table id='%s' %s>", $this->GridId, is_null($this->CssClass) ? "" : "class='" . $this->CssClass . "'");
		print("<thead><tr>");
		for ($iColCounter = 0; $iColCounter < $this->ColumnCount; $iColCounter ++)
		{
			if ($this->isColumnVisible($this->ColumnNames[$iColCounter]))
			{
				print("<th");
				if ($this->MarkColumnsCss)
				{						
					printf(" class='%s'", str_replace(array(" ", ",", "#", "@"), '', trim($this->ColumnNames[$iColCounter])));
					
				}
				print(">" . $this->ColumnTitles[$iColCounter] . "</th>");
			}
		}
		print("</tr></thead>\n");

		if ($this->ColumnCount > 0 && $this->DisplayColumnNamesInFooter)
		{
			print("<tfoot><tr>");
			for ($iColCounter = 0; $iColCounter < $this->ColumnCount; $iColCounter ++)
			{
				if ($this->isColumnVisible($this->ColumnNames[$iColCounter]))
				{
					print("<td");
					if ($this->MarkColumnsCss)
					{						
						printf(" class='%s'", str_replace(array(" ", ",", "#", "@"), '', trim($this->ColumnNames[$iColCounter])));
						
					}
					print(">" . $this->ColumnTitles[$iColCounter] . "</td>");
				}
			}
			print("</tr></tfoot>\n");
		}
		
		print("<tbody>\n");

		$isEvenRow = false;
		
		foreach($this->GridData as $RowMarker => $RowData)
		{
			print("<tr");
			if ($this->MarkAlternateRowsCss)
			{
				print(" class='" . ($isEvenRow ? $this->EvenRowCss : $this->OddRowCss) . "'");
			}
			print(">");
			
			foreach($this->ColumnNames as $ColumnNameIndex => $ColumnName)
			{
				if ($this->isColumnVisible($ColumnName))
				{
					print("<td");
					if ($this->MarkColumnsCss)
					{
						printf(" class='%s'", str_replace(array(" ", ",", "#", "@"), '', trim($this->ColumnNames[$ColumnNameIndex])));
					}
					print(">" . $RowData[$this->ColumnNames[$ColumnNameIndex]]. "</td>");
				}
			}

			print("</tr>\n");
			
			$isEvenRow = !$isEvenRow;
		}
		print("</tbody>");
		print("</table>");
	}
	
	/**
	 * Deprecated
	 * Displays the SuperGrid data.
	 *
	 * @return void
	 */	
	private function x_display()
	{
		if ($this->ColumnCount == 0)
		{
			throw new Exception("SuperGrid does not contain any columns to display");
		}
		
		printf("<table id='%s' %s>", $this->GridId, is_null($this->CssClass) ? "" : "class='" . $this->CssClass . "'");
		print("<thead><tr>");
		for ($iColCounter = 0; $iColCounter < $this->ColumnCount; $iColCounter ++)
		{
			print("<th");
			if ($this->MarkColumnsCss)
			{						
				printf(" class='%s'", str_replace(array(" ", ",", "#", "@"), '', trim($this->ColumnNames[$iColCounter])));
						
			}
			print(">" . $this->ColumnTitles[$iColCounter] . "</th>");
		}
		print("</tr></thead>\n");

		if ($this->ColumnCount > 0 && $this->DisplayColumnNamesInFooter)
		{
			print("<tfoot><tr>");
			for ($iColCounter = 0; $iColCounter < $this->ColumnCount; $iColCounter ++)
			{
				print("<td");
				if ($this->MarkColumnsCss)
				{						
					printf(" class='%s'", str_replace(array(" ", ",", "#", "@"), '', trim($this->ColumnNames[$iColCounter])));
						
				}
				print(">" . $this->ColumnTitles[$iColCounter] . "</td>");
			}
			print("</tr></tfoot>\n");
		}
			
		print("<tbody>\n");

		$isEvenRow = false;
			
		foreach($this->GridData as $RowMarker => $RowData)
		{
			print("<tr");
			if ($this->MarkAlternateRowsCss)
			{
				print(" class='" . ($isEvenRow ? $this->EvenRowCss : $this->OddRowCss) . "'");
			}
			print(">");
				
			$ColumnCounter = 0;
				
			foreach($RowData as $ColumnMarker => $ColumnData)
			{
				print("<td");
				if ($this->MarkColumnsCss)
				{						
					printf(" class='%s'", str_replace(array(" ", ",", "#", "@"), '', trim($this->ColumnNames[$ColumnCounter])));
						
				}
				print(">" . $ColumnData . "</td>");
					
				$ColumnCounter++;
			}
			print("</tr>\n");
				
			$isEvenRow = !$isEvenRow;
		}
		print("</tbody>");
		print("</table>");
	}
}
?>