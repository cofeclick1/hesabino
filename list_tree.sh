#!/bin/bash  

# Check if a directory is provided  
if [ -z "$1" ]; then  
  echo "Usage: ./list_tree.sh /path/to/directory"  
  exit 1  
fi  

# Use the tree command to display files and directories without additional information  
tree -f --noreport "$1"  