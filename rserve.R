# This is a example file to run a very basic Rserve instance
# You can use it by running 
# Rscript rserve.R
# It will launch Rserve and wait for connection
# In some installation  the R command is available
# R CMD Rserve 
# @see Rserve doc
if(length(find.package("Rserve", quiet=TRUE)) == 0) {
    install.packages("Rserve")
}
library(Rserve)
run.Rserve()