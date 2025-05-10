import React, { useMemo, useEffect, useState } from 'react';
import Select, { components } from 'react-select';

// Custom ClearIndicator to clear all selected values
const ClearIndicator = (props) => {
  const {
    clearValue, // Function to clear all selected values
    innerProps: { ref, ...restInnerProps },
  } = props;

  return (
    <div
      {...restInnerProps}
      ref={ref}
      onClick={() => {
        // console.log('ClearIndicator clicked');
        clearValue(); // Clear the select component's internal state
        props.setSelectedFormValues([]); // Clear all values in the state
        props.setSelectedDataFormValues([]); // Clear all data form values
        props.setManualClear(true); // Set manual clear flag
        // console.log('Cleared all selected values');
      }}
      style={{ cursor: 'pointer' }}
    >
      <span style={{ fontSize: '16px' }}>✖</span> {/* Custom X (clear) icon */}
    </div>
  );
};

// FilterType component
const FilterType = ({
  selectedFormValues,
  setFilterMode,
  setSelectedFormValues,
  setSelectedDataFormValues,
}) => {
  // Define individual options (no "all" option)
  const options = useMemo(() => [
    { value: 'jpg', label: 'JPG' },
    { value: 'gif', label: 'GIF' },
    { value: 'png', label: 'PNG' },
    { value: 'video', label: 'Video' },
    { value: 'misc', label: 'Misc' },
    { value: 'audio', label: 'Audio' },
  ], []);

  // State to control the open/close of the select dropdown
  const [menuIsOpen, setMenuIsOpen] = useState(false);
  const [manualClear, setManualClear] = useState(false);

  // Initialize all options as selected when the component first renders
  useEffect(() => {
    // console.log('useEffect triggered');
    // console.log('Selected Form Values:', selectedFormValues);
    // console.log('Manual Clear Flag:', manualClear);

    if (!manualClear && (!selectedFormValues || selectedFormValues.length === 0)) {
      // console.log('Preselecting all options');
    //   setSelectedFormValues(options);
    //   setSelectedDataFormValues(options.map(option => option.value));
    }
    
    // Check if selectedFormValues exists and contains the 'all' option
    if (!manualClear && selectedFormValues && selectedFormValues.length > 0 && selectedFormValues[0]?.value === 'all') {
      console.log('"All" option is selected, preselecting all options again');
      setSelectedFormValues(options);
      setSelectedDataFormValues(options.map(option => option.value));
    }

    // Reset the manualClear flag after handling
    if (manualClear) {
      // console.log('Resetting manual clear flag');
      setManualClear(false);
    }
  }, [options, selectedFormValues, setSelectedFormValues, setSelectedDataFormValues, manualClear]);

  // Function to toggle the dropdown
  const toggleMenuIsOpen = () => {
    setMenuIsOpen((prevState) => !prevState);
  };

  return (
    <div className="select-container">
      <button className="add-button" onClick={toggleMenuIsOpen}>
        <span>+</span>
      </button>
      <Select
        closeMenuOnSelect={false}
        value={selectedFormValues} // Preselect all options
        isMulti
        options={options}
        isSearchable={false} // Prevent typing in the input field
        styles={{
            control: (provided, state) => ({
              ...provided,
              boxShadow: 'none', // Remove the box shadow
              borderColor: state.isFocused ? 'transparent' : provided.borderColor, // Remove the border when focused
              '&:hover': {
                borderColor: 'transparent', // Ensure the border is also removed on hover
              },
            }),
          }}
        menuIsOpen={menuIsOpen} // Control menu open/close state
        onChange={(selected) => {
          // console.log('Select onChange triggered');
          // console.log('Selected Values:', selected);
          // setFilterMode('all'); // Set filter mode to 'all'

          setFilterMode('large'); // Set filter mode to 'all'

          // Update the selected values (will handle both adding and removing)
          setSelectedFormValues(selected || []); // Ensure empty array if no selection

          // If no items are selected, set an empty array, otherwise update with selected values
          const newDataArr = selected ? selected.map(option => option.value) : [];
          setSelectedDataFormValues(newDataArr);

          // Close the menu if all items are cleared
          if (!selected || selected.length === 0) {
            console.log('All items cleared, closing menu');
            setMenuIsOpen(false);
          }
        }}
        components={{
          DropdownIndicator: () => null, // Disable caret by setting DropdownIndicator to null
          ClearIndicator: (props) => (
            <ClearIndicator
              {...props}
              setSelectedFormValues={setSelectedFormValues}
              setSelectedDataFormValues={setSelectedDataFormValues}
              setManualClear={setManualClear} // Pass function to set manual clear flag
            />
          ),
        }}
        noOptionsMessage={() => "No media formats available"} // Customize the "No options" message
        onMenuClose={() => {
          // console.log('Menu closed');
          setMenuIsOpen(false);
        }} // Ensure menu closes when selection is made
      />
    </div>
  );
};

export default FilterType;
