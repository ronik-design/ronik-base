import React from 'react';
import parse from 'html-react-parser';

const ContentBlock = ({ mode="light", className="", title, description }) => {
    let modeClass = mode === "light" ? "light-mode" : "dark-mode";
    return (
        <div className={`content-block ${className} content-block--${modeClass}`}>
		<div className={`content-block__inner content-block__inner--${modeClass}`}>
			<div className={`content-block__content content-block__content--${modeClass}`}>
				{title && <h1>{parse(title)}</h1>}
				{description && <p>{parse(description)}</p>}
			</div>
		</div>
	</div>
);
};

export default ContentBlock;