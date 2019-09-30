import os
os.environ["OMP_NUM_THREADS"] = "1" # export OMP_NUM_THREADS=4
os.environ["OPENBLAS_NUM_THREADS"] = "1" # export OPENBLAS_NUM_THREADS=4
os.environ["MKL_NUM_THREADS"] = "1" # export MKL_NUM_THREADS=6
os.environ["VECLIB_MAXIMUM_THREADS"] = "1" # export VECLIB_MAXIMUM_THREADS=4
os.environ["NUMEXPR_NUM_THREADS"] = "1" # export NUMEXPR_NUM_THREADS=6

import sys
import numpy as np
import scipy.spatial.distance
import json


def cos_cdist(matrix, vector):
    """
        Compute the cosine distances between each row of matrix and vector.
        """
    v = vector.reshape(1, -1)
    result = scipy.spatial.distance.cdist(matrix, v, 'cosine').reshape(-1)
    result[np.isnan(result)] = 1
    return result

pref_matrix = np.loadtxt(open("python/preferences.csv", "rb"), delimiter=",")
id_matrix = np.loadtxt(open("python/identities.csv", "rb"), delimiter=",")
ids = np.loadtxt(open("python/ids.csv", "rb"), delimiter=",")
id = int(sys.argv[1])
index = np.where(ids==id)
mypref = pref_matrix[index]
myids = id_matrix[index]
#print mypref
print json.dumps(ids.tolist())
print json.dumps(cos_cdist(id_matrix,mypref).tolist())
print json.dumps(cos_cdist(pref_matrix,myids).tolist())
