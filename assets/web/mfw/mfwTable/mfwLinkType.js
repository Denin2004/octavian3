import React, {Component} from 'react';

class MfwLinkType {
    static render(text, record, index, column) {
        return <a>{text}</a>;
    }
    
    static renderFilter(row, column) {
        return row[column.dataIndex];
    }        
};

export default MfwLinkType;