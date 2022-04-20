import React, {Component} from 'react';

import {message} from 'antd';
import axios from 'axios';

class MfwLinkType {
    static render(text, record, index, column) {
        return column.response != undefined ? <a onClick={() => {
            axios({
                url: record[column.dataIndex+'_LINK'], 
                method: column.action.attrs && column.action.attrs['data-mfw_method'] ? column.action.attrs['data-mfw_method'] : 'get',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(res => {
                column.response(res.data);
            }).catch(error => {
                message.error(error.toString());
            });            
        }}>{text}</a> : <a href={record[column.dataIndex+'_LINK']} target="_blank">{text}</a>;
    }
    
    static renderFilter(row, column) {
        return row[column.dataIndex];
    }        
};

export default  MfwLinkType;