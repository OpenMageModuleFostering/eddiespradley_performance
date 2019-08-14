<?php

# app/code/community/eddiespradley/performance/Block/Left.php

class Eddiespradley_Performance_Block_Main extends Mage_Core_Block_Template
{
    
	var $phpMinVersion = '5.4.0';
	var $mysqlMinVersion = '5.6.0';


    function getPhpVersion()
    {
    	return PHP_VERSION;
    }
    
    function getPhpMemory()
    {
        return ini_get('memory_limit');
    }
    
    function getPhpInputVars()
    {
        return ini_get('max_input_vars');
    }

    function getPhpRecommendation()
    {
    	$diff = version_compare(PHP_VERSION, $this->phpMinVersion);

    	if($diff < 0)
    		return "Keeping PHP up-to-date has huge impact on Magento speed.  You should upgrade to {$this->phpMinVersion}"; 


    	return "Your PHP version appeasrs to be good."; 
    }


    function getMysqlVersion()
    {
    	$vers = Mage::getModel('esperformance/versions');
    	return $vers->getMysqlVersion();
    }

    function getMysqlRecommendation()
    {
    	$diff = version_compare($this->getMysqlVersion(), $this->mysqlMinVersion);

    	if($diff < 0)
    		return "Keeping MySQL up-to-date has a huge impact on Magento spped.  You should upgrade to {$this->mysqlMinVersion}";

    	return "Your MySQL Version appears to be in good shape.";
    }

    function getServerType()
    {
    	if($this->isApache())
    		return "Apache";
    	else if($this->isNginx)
    		return "Nginx";
    	else
    		return "Unknown";
    }

    function isApache()
    {
    	return $apache = strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache') !== FALSE;
    }

    function isNginx()
    {
    	return $nginx = strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'ngix') !== FALSE;
    }

    function getFreeDiskSpace()
    {
    	$bytes = disk_free_space("/"); 
    	return $this->transformBytes($bytes);
    }

    function getTotalDiskSpace()
    {
    	$bytes = disk_total_space("/"); 
    	return $this->transformBytes($bytes);
    }


    function transformBytes($bytes)
    {
    	if($bytes < 1024)
    		return;

    	$si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
	    $base = 1024;
	    $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
	   
	    return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
    }

    function getDirectorySize($directory)
    {
	    $size = 0;
	    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
	        $size+=$file->getSize();
	    }
	    return $this->transformBytes($size);
    }

    function getCPU()
    {
	    $load = sys_getloadavg();
	    return $load;
    }

    function getMemoryUsage()
    {
	    $free = shell_exec('free');
	    $free = (string)trim($free);
	    $free_arr = explode("\n", $free);
	    $mem = explode(" ", $free_arr[1]);
	    $mem = array_filter($mem);
	    $mem = array_merge($mem);
	    $memory_usage = $mem[2]/$mem[1]*100;

	    return $memory_usage;
    }

    function getCPUs()
	{
	  $numCpus = 1;
	 
	  if (is_file('/proc/cpuinfo'))
	  {
	    $cpuinfo = file_get_contents('/proc/cpuinfo');
	    preg_match_all('/^processor/m', $cpuinfo, $matches);
	 
	    $numCpus = count($matches[0]);
	  }
	  else if ('WIN' == strtoupper(substr(PHP_OS, 0, 3)))
	  {
	    $process = @popen('wmic cpu get NumberOfCores', 'rb');
	 
	    if (false !== $process)
	    {
	      fgets($process);
	      $numCpus = intval(fgets($process));
	 
	      pclose($process);
	    }
	  }
	  else
	  {
	    $process = @popen('sysctl -a', 'rb');
	 
	    if (false !== $process)
	    {
	      $output = stream_get_contents($process);
	 
	      preg_match('/hw.ncpu: (\d+)/', $output, $matches);
	      if ($matches)
	      {
	        $numCpus = intval($matches[1][0]);
	      }
	 
	      pclose($process);
	    }
	  }
	  
	  return $numCpus;
	}

	function getIOWait()
	{
		 $a=$this->procStats(); 
		  sleep(5); 
		 $b=$this->ProcStats(); 

		 $total=array_sum($b)-array_sum($a); 
		   
		 $loadavg = round(100* (($b[0]+$b[1]+$b[2]) - ($a[0]+$a[1]+$a[2])) / $total, 2); // user+nice+system 
		 $iowait= round(100* ($b[4] - $a[4])/$total,2); 
		 return $iowait;
	}

	 function procStats() 
	   {    
	       $fp=fopen("/proc/stat","r"); 
	       if(false===$fp) 
	               return false; 
	       $a=explode(' ',fgets($fp)); 
	       array_shift($a); //get rid of 'cpu' 
	       while(!$a[0]) 
	           array_shift($a); //get rid of ' ' 
	       var_dump($a); 
	       fclose($fp); 
	       return $a; 
	   } 

	   function getDatabaseSize()
	   {	
	    	$vers = Mage::getModel('esperformance/versions');
	    	return $vers->getMysqlSize();
	   }

	   function isPercona()
	   {
			$vers = Mage::getModel('esperformance/versions');
    		
    		return strpos(strtolower($vers->getMysqlVersionNotes()), 'percona') !== FALSE;
	   		
	   }
    
	   function getModulesInfo()
	   {
	       $active = 0;
	       $inactive = 0;
	       $total = 0;
	       $core_active = 0;
	       foreach(Mage::getConfig()->getNode('modules')->children() as $key=>$value)
	       {
	           $value = (array)$value;
	    
	           $total++;
	           
	           if($value['codePool'] == "core" && $value['active'] === 'true')
	               $core_active++;
	               
	           ($value['active'] === "true" ? $active++ : $inactive++ );

	        }  
	       
	        return array(
	           'core_active'   => $core_active,
	           'total_active'  => $active,
	           'inactive'      => $inactive,
	           'installed'     => $total
	        );
	        
	   }
	   
	   function detectCdn()
	   {
	       $skin  = Mage::getStoreConfig('web/unsecure/base_skin_url');
	       $media = Mage::getStoreConfig('web/unsecure/base_media_url');
	       $js    = Mage::getStoreConfig('web/unsecure/base_js_url');
	       $base  = Mage::getStoreConfig('web/unsecure/base_link_url');
	       
	       
	       
	       $data['skin'] = "not detected";
	       
	   
	       
	       if(Mage::getStoreConfig('web/unsecure/base_skin_url') != $base."skin/")
	           $data['skin'] = "active!";
	       
	       $data['media'] = "not detected";
	       if(Mage::getStoreConfig('web/unsecure/base_media_url') != $base."media/")
	           $data['media'] = "active!";
	       
	       $data['js'] = "not detected";
	       if(Mage::getStoreConfig('web/unsecure/base_js_url') != $base."js/")
	           $data['js'] = "active!";
	       
	       return $data;
	       

	   }


}

