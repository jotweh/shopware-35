<?php
class MyDestinationFile extends Destination {
  /**
   * @var String result file name / path
   * @access private
   */
  var $_dest_filename;

  function MyDestinationFile($dest_filename) {
    $this->_dest_filename = $dest_filename;
  }

  function process($tmp_filename, $content_type) {
    copy($tmp_filename, $this->_dest_filename);
  }
}

class MyFetcherLocalFile extends Fetcher {
  var $_content;

  function MyFetcherLocalFile($file) {
    //$this->_content = file_get_contents($file);
    $this->_content = $file;
  }

  function get_data($dummy1) {
    return new FetchedDataURL($this->_content, array(), "");
  }

  function get_base_url() {
    return dirname(__FILE__).'/';
  }
}