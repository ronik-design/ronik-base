import React, { useMemo } from 'react';
import Select from 'react-select';
import parse from 'html-react-parser'; // Importing HTML parser

import FilterType from './MediaFilter';

// Function to get the value of a query parameter from the URL
function getQueryParameter(name) {
  const urlParams = new URLSearchParams(window.location.search);
  return urlParams.get(name);
}

// Add this copy function near the top of the file
const copyToClipboard = (text) => {
  // Create a temporary textarea element
  const textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.style.position = 'fixed'; // Prevent scrolling to bottom
  textarea.style.opacity = '0';
  document.body.appendChild(textarea);

  try {
    // Select and copy the text
    textarea.select();
    document.execCommand('copy');
    
    // Show feedback on the button
    const button = document.querySelector(`button[data-url="${text}"]`);
    if (button) {
      const originalText = button.textContent;
      button.textContent = 'Copied!';
      setTimeout(() => {
        button.textContent = originalText;
      }, 2000);
    }
  } catch (err) {
    console.error('Failed to copy text: ', err);
  } finally {
    // Clean up
    document.body.removeChild(textarea);
  }
};

// Helper function to handle pagination logic
const getPaginatedData = (data, page, itemsPerPage) => {
  return data.reduce((resultArray, item, index) => {
    const chunkIndex = Math.floor(index / itemsPerPage);
    if (!resultArray[chunkIndex]) {
      resultArray[chunkIndex] = [];
    }
    resultArray[chunkIndex].push(item);
    return resultArray;
  }, [])[page] || [];
};

// FilterNav component
const FilterNav = ({
  mediaCollector,
  filterMode,
  filter_size,
}) => {
  const totalSize = mediaCollector.reduce((acc, item) => {
    let size = parseFloat(item['media_size']);
    if (item['media_size'].includes('KB')) size /= 1024;
    if (item['media_size'].includes('GB')) size *= 1000;
    if (item['media_size'].includes('bytes')) size *= 1e-6;
    return acc + (isNaN(size) ? 0 : size);
  }, 0);

  return (
    <>
      <div className='filter-nav'>
        {/* <button
          type="button"
          title="Filter All"
          onClick={filter_size}
          data-filter="all"
          className={`filter-nav__button filter-nav__button--${filterMode === 'all' ? 'active' : 'inactive'}`}
        >
          Filter All
        </button> */}
        <button
          type="button"
          title="Sort Smallest to Largest File Size"
          onClick={filter_size}
          data-filter="small"
          className={`filter-nav__button filter-nav__button-sort filter-nav__button--${filterMode === 'small' ? 'active' : 'inactive'}`}
        >
          Sort Smallest to Largest File Size
        </button>

        <button
          type="button"
          title="Sort Largest to Smallest File Size"
          onClick={filter_size}
          data-filter="large"
          className={`filter-nav__button filter-nav__button-sort filter-nav__button--${filterMode === 'large' || filterMode === 'all' ? 'active' : 'inactive'}`}
        >
          Sort Largest to Smallest File Size
        </button>

      </div>
      {/* <span className="overall-number">Number of unlinked files found: {mediaCollector.length}</span>
      <span className="overall-number">Total unlinked media file size: {Math.round(totalSize * 100) / 100} MB</span> */}
    </>
  );
};


// FilterNav component
const FilterNavData = ({
  mediaCollector
}) => {
  const totalSize = mediaCollector.reduce((acc, item) => {
    let size = parseFloat(item['media_size']);
    if (item['media_size'].includes('KB')) size /= 1024;
    if (item['media_size'].includes('GB')) size *= 1000;
    if (item['media_size'].includes('bytes')) size *= 1e-6;
    return acc + (isNaN(size) ? 0 : size);
  }, 0);

  return (
    <>
      <span className="overall-number">Number of unlinked files found: {mediaCollector.length}</span>
      <span className="overall-number">Total unlinked media file size: {Math.round(totalSize * 100) / 100} MB</span>
    </>
  );
};



// PagerNav component
const PagerNav = ({ pager, setFilterPager, mediaCollector = [], itemsPerPage }) => {
  const totalItems = mediaCollector.length || 0;
  const totalPages = Math.ceil(totalItems / itemsPerPage);

  const pageNumbers = [...Array(totalPages).keys()]; // Generates [0, 1, 2, ...]

  return (
    <div className='filter-nav filter-nav--no-space-top'>
      <p>Showing {pager * itemsPerPage + 1}-{Math.min((pager + 1) * itemsPerPage, totalItems)} of {totalItems}</p>
      <div className='pagination'>
        {pageNumbers.map(pageNumber => (
          <button
            key={pageNumber}
            className={`filter-nav__button ${pageNumber === pager ? 'filter-nav__button--active' : ''}`}
            onClick={() => setFilterPager(pageNumber)}
          >
            {pageNumber + 1}
          </button>
        ))}
      </div>
    </div>
  );
};

// MediaCollectorTable component
const MediaCollectorTable = ({
  type,
  mediaCollector,
  selectedFormValues,
  filterMode,
  setFilterMode,
  setFilterPager,
  setSelectedFormValues,
  setSelectedDataFormValues,
  filter_size,
  filterPager,
  mediaCollectorHigh,
  mediaCollectorLow,
  activateDelete,
  activatePreserve
}) => {
  if (type === 'preserved') return null;

  if (!mediaCollector || mediaCollector === 'no-images') {
    const f_wpwrap = document.querySelector("#wpwrap");
    const element = document.getElementsByClassName("centered-blob");
    if (f_wpwrap) f_wpwrap.classList.remove('loader');
    if (element[0]) element[0].remove();
    if (element[1]) element[1].remove();
    return (
      <>
        <FilterType
          selectedFormValues={selectedFormValues}
          setFilterMode={setFilterMode}
          setSelectedFormValues={setSelectedFormValues}
          setSelectedDataFormValues={setSelectedDataFormValues}
        />
        <p>No Media Found!</p>
      </>
    );
  }

  const page = parseInt(filterPager) || 0;


  let itemsPerPage = 20;
  const mediaId = getQueryParameter('media_id');
  if(mediaId){
    itemsPerPage = 2000;
  }

  const output = getPaginatedData(
    filterMode === 'high' ? mediaCollectorHigh : filterMode === 'low' ? mediaCollectorLow : mediaCollector,
    page,
    itemsPerPage
  );

  const mediaCollectorItems = output.map(collector => (
    <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
      <td className='media-collector-table__td'>
        <button onClick={activateDelete} data-delete-media={collector['id']}>
          <img src={`/wp-content/plugins/${document.querySelector(type === 'preserved' ? '#ronik-base_media_cleaner_preserved' : '#ronik-base_media_cleaner')?.getAttribute("data-plugin-name")}/admin/media-cleaner/image/big-trash-can.svg`} alt="Delete" />
        </button>
      </td>
      <td className='media-collector-table__td media-collector-table__td--img-thumb'>{parse(collector['img-thumb'])}</td>
      <td className='media-collector-table__td file-type'>{collector['media_file_type']}</td>
      <td className='media-collector-table__td file-size'>{collector['media_size']}</td>
      <td className='media-collector-table__td'>{collector['id']}</td>
      <td className='media-collector-table__td'>
        <a target="_blank" rel="noopener noreferrer" href={`/wp-admin/post.php?post=${collector['id']}&action=edit`}>Go to media</a>
      </td>
      <td className='media-collector-table__td media-collector-table__td--img-url'>
        <button 
          onClick={() => copyToClipboard(collector['media_file'])}
          data-url={collector['media_file']}
          className="copy-url-button"
        >
          Copy URL
        </button>
      </td>
      <td className='media-collector-table__td media-collector-table__td--preserve'>
        <button onClick={activatePreserve} data-preserve-media={collector['id']}>Preserve File</button>
      </td>
    </tr>
  ));

  return (
    <>
        <p>Filter by file type</p>
      <FilterType
        selectedFormValues={selectedFormValues}
        setFilterMode={setFilterMode}
        setSelectedFormValues={setSelectedFormValues}
        setSelectedDataFormValues={setSelectedDataFormValues}
      />
      <FilterNavData
        mediaCollector={mediaCollector}
        filterMode={filterMode}
        filter_size={filter_size}
      />
      <PagerNav
        pager={filterPager}
        setFilterPager={setFilterPager}
        mediaCollector={mediaCollector}
        itemsPerPage={itemsPerPage}
      />
      <table className='media-collector-table'>
        <tbody className='media-collector-table__tbody'>
          <tr className='media-collector-table__tr'>
            <th className='media-collector-table__th'>Permanently Delete</th>
            <th className='media-collector-table__th media-collector-table__th--img-thumb'>Thumbnail Image</th>
            <th className='media-collector-table__th'>File Type</th>
            <th className='media-collector-table__th'>File Size 
            <FilterNav
              mediaCollector={mediaCollector}
              filterMode={filterMode}
              filter_size={filter_size}
            />
            </th>
            <th className='media-collector-table__th'>File ID</th>
            <th className='media-collector-table__th'>Media Library Link</th>
            <th className='media-collector-table__th media-collector-table__th--img-url'>File Path</th>
            <th className='media-collector-table__th media-collector-table__th--preserve'>Preserve</th>
          </tr>
          {mediaCollectorItems}
        </tbody>
      </table>
      <PagerNav
        pager={filterPager}
        setFilterPager={setFilterPager}
        mediaCollector={mediaCollector}
        itemsPerPage={itemsPerPage}
      />
    </>
  );
};

// PreservedMediaCollectorTable component
const PreservedMediaCollectorTable = ({
  type,
  mediaCollectorPreserved,
  activatePreserve,
  selectedFormValues,
  filterMode,
  setFilterMode,
  setFilterPager,
  setSelectedFormValues,
  setSelectedDataFormValues,
  filter_size,
  filterPager,
  mediaCollectorHigh,
  mediaCollectorLow,
  activateDelete
}) => {
  if (!mediaCollectorPreserved) return null;

  if(type !== 'preserved'){
    return;
    }


    if (!mediaCollectorPreserved || mediaCollectorPreserved === 'no-images') {
        const f_wpwrap = document.querySelector("#wpwrap");
        const element = document.getElementsByClassName("centered-blob");
        if (f_wpwrap) f_wpwrap.classList.remove('loader');
        if (element[0]) element[0].remove();
        if (element[1]) element[1].remove();
        return (
          <>
            <FilterType
              selectedFormValues={selectedFormValues}
              setFilterMode={setFilterMode}
              setSelectedFormValues={setSelectedFormValues}
              setSelectedDataFormValues={setSelectedDataFormValues}
            />
            <p>No Media Found!</p>
          </>
        );
      }


  const page = parseInt(filterPager) || 0;
  // const itemsPerPage = 20;
  let itemsPerPage = 20;
  const mediaId = getQueryParameter('media_id');
  if(mediaId){
    itemsPerPage = 2000;
  }
  const output = getPaginatedData(
    filterMode === 'high' ? mediaCollectorHigh : filterMode === 'low' ? mediaCollectorLow : mediaCollectorPreserved,
    page,
    itemsPerPage
  );

  const mediaCollectorItems = output.map(collector => (
    <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
      <td className='media-collector-table__td media-collector-table__td--img-thumb'>{collector['img-thumb'] ? parse(collector['img-thumb']) : 'No Image Found'}</td>
      <td className='media-collector-table__td file-type'>{collector['media_file_type']}</td>
      <td className='media-collector-table__td file-size'>{collector['media_size']}</td>
      <td className='media-collector-table__td'>{collector['id']}</td>
      <td className='media-collector-table__td'>
        <a target="_blank" rel="noopener noreferrer" href={`/wp-admin/post.php?post=${collector['id']}&action=edit`}>Go to media</a>
      </td>
      <td className='media-collector-table__td media-collector-table__td--img-url'>
        <button 
          onClick={() => copyToClipboard(collector['media_file'])}
          data-url={collector['media_file']}
          className="copy-url-button"
        >
          Copy URL
        </button>
      </td>
      <td className='media-collector-table__td media-collector-table__td--unpreserve '>
        <button onClick={activatePreserve} data-unpreserve-media={collector['id']}>Un-preserve file</button>
      </td>
    </tr>
  ));

  return (
    <>
      <h1>Preserved Files</h1>

        <p>Filter by file type</p>
      <FilterType
        selectedFormValues={selectedFormValues}
        setFilterMode={setFilterMode}
        setSelectedFormValues={setSelectedFormValues}
        setSelectedDataFormValues={setSelectedDataFormValues}
      />
      <FilterNavData
        mediaCollector={mediaCollectorPreserved}
        filterMode={filterMode}
        filter_size={filter_size}
      />
      <PagerNav
        pager={filterPager}
        setFilterPager={setFilterPager}
        mediaCollector={mediaCollectorPreserved}
        itemsPerPage={itemsPerPage}
      />
      <table className='media-collector-table'>
        <tbody className='media-collector-table__tbody'>
          <tr className='media-collector-table__tr'>
            <th className='media-collector-table__th media-collector-table__th--img-thumb'>Thumbnail Image</th>
            <th className='media-collector-table__th'>File Type</th>
            <th className='media-collector-table__th'>File Size
            <FilterNav
              mediaCollector={mediaCollectorPreserved}
              filterMode={filterMode}
              filter_size={filter_size}
            />
            </th>
            <th className='media-collector-table__th'>File ID</th>
            <th className='media-collector-table__th'>Media Library Link</th>
            <th className='media-collector-table__th media-collector-table__th--img-url'>File Path</th>
            <th className='media-collector-table__th media-collector-table__th--unpreserve'>Temporarily Preserve</th>
          </tr>
          {mediaCollectorItems}
        </tbody>
      </table>
      <PagerNav
        pager={filterPager}
        setFilterPager={setFilterPager}
        mediaCollector={mediaCollectorPreserved}
        itemsPerPage={itemsPerPage}
      />
    </>
  );
};

// Export components
export default { MediaCollectorTable, PreservedMediaCollectorTable };
