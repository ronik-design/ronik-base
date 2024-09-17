import React from 'react';
import Select from 'react-select';
import parse from 'html-react-parser'; // Importing HTML parser


// MediaCollectorTable component
const MediaCollectorTable = ({
    mediaCollector,
    selectedFormValues,
    filterMode,
    setFilterMode,
    setFilterPager,
    setSelectedFormValues,
    setSelectedDataFormValues,
    filter_size,
    filterPager,
    filterType,
    mediaCollectorHigh,
    mediaCollectorLow,
    activateDelete,
    activatePreserve
}) => {

    // Options for the select input
    const options = [
        { value: 'all', label: 'All' },
        { value: 'jpg', label: 'JPG' },
        { value: 'gif', label: 'GIF' },
        { value: 'png', label: 'PNG' },
        { value: 'video', label: 'Video' },
        { value: 'misc', label: 'Misc' }
    ];

    // FilterType component
    const FilterType = () => (
        <div className="select select-multiple">
            <Select
                closeMenuOnSelect={false}
                defaultValue={selectedFormValues}
                isMulti
                options={options}
                onChange={(e) => {
                    setFilterMode('all');
                    const params = new URLSearchParams(window.location.search);
                    const paramsObj = Array.from(params.keys()).reduce(
                        (acc, val) => ({ ...acc, [val]: params.get(val) }),
                        {}
                    );
                    if (window.history.pushState) {
                        const newURL = new URL(window.location.href);
                        newURL.search = `?page=options-ronik-base_media_cleaner&page_number=${paramsObj['page_number']}`;
                        window.history.pushState({ path: newURL.href }, '', newURL.href);
                    }
                    const newArr = e.map(option => option);
                    setSelectedFormValues(newArr);
                    const newDataArr = e.map(option => option.value);
                    setSelectedDataFormValues(newDataArr);
                }}
            />
        </div>
    );


    // Guard clause to handle cases where mediaCollector is 'no-images' or null
    if (!mediaCollector || mediaCollector === 'no-images') {
        // Remove loading elements from the DOM if no images are found
        const f_wpwrap = document.querySelector("#wpwrap");
        const element = document.getElementsByClassName("centered-blob");
        if (f_wpwrap) f_wpwrap.classList.remove('loader');
        if (element[0]) element[0].remove(); // Removes the 'centered-blob' div
        if (element[1]) element[1].remove(); // Removes the second 'centered-blob' div
        return (
            <>
                <FilterType filterType={filterType} />
                <p>No Media Found!</p>
            </>
        );
    }


    // Pagination setup
    const urlParams = new URLSearchParams(window.location.search);
    const page = parseInt(filterPager) || 0;
    const itemsPerPage = 20;
    const paginatedData = (data) => data.reduce((resultArray, item, index) => {
        const chunkIndex = Math.floor(index / itemsPerPage);
        if (!resultArray[chunkIndex]) {
            resultArray[chunkIndex] = []; // Start a new chunk
        }
        resultArray[chunkIndex].push(item);
        return resultArray;
    }, []);

    // Determine which media collector data to use based on filterMode
    let output = paginatedData(mediaCollector)[page];
    if (filterMode === 'high' && mediaCollectorHigh) {
        output = paginatedData(mediaCollectorHigh)[page];
    } else if (filterMode === 'low' && mediaCollectorLow) {
        output = paginatedData(mediaCollectorLow)[page];
    }


    // FilterNav component
    const FilterNav = () => {
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
                    <button
                        type="button"
                        title="Filter All"
                        onClick={filter_size}
                        data-filter="all"
                        className={`filter-nav__button filter-nav__button--${filterMode === 'all' ? 'active' : 'inactive'}`}
                    >
                        Filter All
                    </button>
                    <button
                        type="button"
                        title="Sort Largest to Smallest File Size"
                        onClick={filter_size}
                        data-filter="high"
                        className={`filter-nav__button filter-nav__button--${filterMode === 'high' ? 'active' : 'inactive'}`}
                    >
                        Sort Largest to Smallest File Size
                    </button>
                    <button
                        type="button"
                        title="Sort Smallest to Largest File Size"
                        onClick={filter_size}
                        data-filter="low"
                        className={`filter-nav__button filter-nav__button--${filterMode === 'low' ? 'active' : 'inactive'}`}
                    >
                        Sort Smallest to Largest File Size
                    </button>
                </div>
                <span className="overall-number">Number of unlinked files found: {mediaCollector.length}</span>
                <span className="overall-number">Total unlinked media file size: {Math.round(totalSize * 100) / 100} MB</span>
            </>
        );
    };

    // PagerNav component
    const PagerNav = ({ pager, setFilterPager, mediaCollector = [], itemsPerPage }) => {
        // Calculate total pages
        const totalItems = mediaCollector.length || 0;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        // Generate an array of page numbers
        const pageNumbers = [...Array(totalPages).keys()]; // Generates [0, 1, 2, ...]

        // Event handler for clicking on a page number
        const handlePageClick = (pageNumber) => {
            setFilterPager(pageNumber);
        };

        const startItem = pager * itemsPerPage + 1;
        const endItem = Math.min(startItem + itemsPerPage - 1, totalItems);

        return (
            <div className='filter-nav filter-nav--no-space-top'>
                <p>Showing {startItem}-{endItem} of {totalItems}</p>
                <div className='pagination'>
                    {pageNumbers.map(pageNumber => (
                        <button
                            key={pageNumber}
                            className={`filter-nav__button ${pageNumber === pager ? 'filter-nav__button--active' : ''}`}
                            onClick={() => handlePageClick(pageNumber)}
                        >
                            {pageNumber + 1}
                        </button>
                    ))}
                </div>
            </div>
        );
    };




    // Render media items
    const mediaCollectorItems = output?.map(collector => (
        <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
            <td className='media-collector-table__td'>
                <button onClick={activateDelete} data-delete-media={collector['id']}>
                    <img src={`/wp-content/plugins/${document.querySelector('#ronik-base_media_cleaner').getAttribute("data-plugin-name")}/admin/media-cleaner/image/big-trash-can.svg`} alt="Delete"/>
                </button>
            </td>
            <td className='media-collector-table__td media-collector-table__td--img-thumb'>{parse(collector['img-thumb'])}</td>
            <td className='media-collector-table__td file-type'>{collector['media_file_type']}</td>
            <td className='media-collector-table__td file-size'>{collector['media_size']}</td>
            <td className='media-collector-table__td'>{collector['id']}</td>
            <td className='media-collector-table__td'>
                <a target="_blank" rel="noopener noreferrer" href={`/wp-admin/post.php?post=${collector['id']}&action=edit`}>Go to media</a>
            </td>
            <td className='media-collector-table__td media-collector-table__td--img-url'>{collector['media_file']}</td>
            <td className='media-collector-table__td media-collector-table__td--preserve'>
                <button onClick={activatePreserve} data-preserve-media={collector['id']}>Preserve File</button>
            </td>
        </tr>
    ));


    return (
        <>
            <FilterType />
            <FilterNav />
            <PagerNav pager={filterPager} setFilterPager={setFilterPager} mediaCollector={mediaCollector} itemsPerPage={itemsPerPage} />
            <table className='media-collector-table'>
                <tbody className='media-collector-table__tbody'>
                    <tr className='media-collector-table__tr'>
                        <th className='media-collector-table__th'>Permanently Delete</th>
                        <th className='media-collector-table__th media-collector-table__th--img-thumb'>Thumbnail Image</th>
                        <th className='media-collector-table__th'>File Type</th>
                        <th className='media-collector-table__th'>File Size</th>
                        <th className='media-collector-table__th'>File ID</th>
                        <th className='media-collector-table__th'>Media Library Link</th>
                        <th className='media-collector-table__th media-collector-table__th--img-url'>File Path</th>
                        <th className='media-collector-table__th media-collector-table__th--preserve'>Preserve: <br /><sup>Select preserve to exclude any file from bulk deletion.</sup></th>
                    </tr>
                </tbody>
                <tbody className='media-collector-table__tbody'>
                    {mediaCollectorItems}
                </tbody>
            </table>
        </>
    );
};

// PreservedMediaCollectorTable component
const PreservedMediaCollectorTable = ({ mediaCollectorPreserved, activatePreserve }) => {
    if (!mediaCollectorPreserved) return null;

    // Render preserved media items
    const mediaCollectorItems = mediaCollectorPreserved.map(collector => (
        <tr className='media-collector-table__tr' data-media-id={collector['id']} key={collector['id']}>
            <td className='media-collector-table__td media-collector-table__td--img-thumb'>{collector['img-thumb'] ? parse(collector['img-thumb']) : 'No Image Found'}</td>
            <td className='media-collector-table__td file-type'>{collector['media_file_type']}</td>
            <td className='media-collector-table__td file-size'>{collector['media_size']}</td>
            <td className='media-collector-table__td'>{collector['id']}</td>
            <td className='media-collector-table__td'>
                <a target="_blank" rel="noopener noreferrer" href={`/wp-admin/post.php?post=${collector['id']}&action=edit`}>Go to media</a>
            </td>
            <td className='media-collector-table__td media-collector-table__td--img-url'>{collector['media_file']}</td>
            <td className='media-collector-table__td media-collector-table__td--preserve'>
                <button onClick={activatePreserve} data-unpreserve-media={collector['id']}>Un-preserve file</button>
            </td>
        </tr>
    ));

    return (
        <table className='media-collector-table'>
            <tbody className='media-collector-table__tbody'>
                <tr className='media-collector-table__tr'>
                    <th className='media-collector-table__th media-collector-table__th--img-thumb'>Thumbnail Image</th>
                    <th className='media-collector-table__th'>File Type</th>
                    <th className='media-collector-table__th'>File Size</th>
                    <th className='media-collector-table__th'>File ID</th>
                    <th className='media-collector-table__th'>Media Library Link</th>
                    <th className='media-collector-table__th media-collector-table__th--img-url'>File Path</th>
                    <th className='media-collector-table__th media-collector-table__th--preserve'>Temporarily Preserve Image <br /><sup>Clicking the button will not delete the image, it will just exclude the selected image from the media list temporarily.</sup></th>
                </tr>
            </tbody>
            <tbody className='media-collector-table__tbody'>
                {mediaCollectorItems}
            </tbody>
        </table>
    );
};

// Export components as default with named export syntax
export default { MediaCollectorTable, PreservedMediaCollectorTable };
